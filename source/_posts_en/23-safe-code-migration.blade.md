---
isFeatured: true
slug: safe-code-migration
title: How to safely migrate a database or field in 9 steps
category: guide
createdAt: 2021-12-04
sitemap:
  lastModified: 2021-12-04  
image:
  url: /assets/images/posts/23-safe-code-migration-640.webp
  alt: 'A bunch tools such as a hammers and screwdrivers.'
tags:
  - refactoring
  - languages
  - guide
meta:
  description: "I’m writing about a very specific and hairy problem: unique id migration."
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](https://codamos.com.br/migracao-banco-de-dados.html)

I was writing another post about Feature Flags and decided to use an example that actually required way more than just feature flags. So I’ll just write about it first and maybe I can talk about feature flags exclusively another moment.

## The problem


I’m writing about a very specific and hairy problem: unique id migration.

We have an entity `User`, identified by `User::$id` that looks like this:


```
final class User
{
  public function __construct(
    public int $id,
  ) {}
}
```


And the way you access data from it, is via a repository interface named `UserRepository`. I’ll drop here a simple Sqlite implementation of this repository too:


```
interface UserRepository
{
  /**
   * @throws UserNotFoundException
   */
  public function findById(
    int $id
  ): User;
}

final class SqliteUserRepository
  implements UserRepository
{
  public function findById(
    int $id
  ): User {
    $sql = "...";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      'id' => $id,
    ]);
    // ...
  }
}
```

Pretty simple set up.

Now let’s say that your team decided that a User’s unique identifier, for security reasons, should no  longer be an integer but an UUID should be adopted instead.

Of course no downtime is not acceptable.

## The solution

To be super safe, I’ll implement it in three phases:

1. Let both ids coexist
1. Battle test the UUID implementation
1. Decommission the former integer id implementation

The main reason why I’d like to make it happen in different phases, is that tests aren’t enough to make sure everything works as expected. This field might be used by other jobs via API or something I can’t even imagine right now.

So just in case, I’d like to be able to safely rollback to the previous implementation at any given moment.

**I’m assuming every step I describe here is properly covered with tests, ideally before the refactoring happens.**

## Step 1 - Decouple from primitive types

Whatever you do next, it won’t be easy without this! That `int` primitive type in the `User` class is asking for explosions to happen.

If you want to smoothly transition away from integer to UUID, your best shot will be to first decouple your code from primitives. A way to do it is by encapsulating your primitives. I’m gonna create a class named `UserId` and let the code depend on it instead of `int`:


```
final class UserId
{
  public function __construct(
    public int $id,
  ) {}

  public function getId(): int
  {
    return $this->id;
  }
}

final class User
{
  public function __construct(
    public UserId $id,
  ) {}
}

interface UserRepository
{
  /**
   * @throws UserNotFoundException
   */
  public function findById(
    UserId $id
  ): User;
}
```

The above should make refactoring slightly easier. `UserId` still returns `int` when `getId()` is called, but that’s fine! What matters the most is that our code depends on `UserId` - a type we control - instead of the primitive `integer` - which we don’t control at all.

Now just cover all existing code to use `UserId` instead of `int $id`.

```
final class SqliteUserRepository
  implements UserRepository
{
  public function findById(
    UserId $id
  ): User {
    $sql = "...";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      'id' => $id->getId(),
    ]);
    // ...
  }
}
```

Until here, nothing changed. We’re just setting up the stage.

This feels safe to be merged and deployed, and nothing should break. Tests help a lot, by the way. Do not skip them!

## Step 2 - Let both fields coexist

Now let’s make sure that we can add a new field to our `Users` table. Something like this would do:

```
sqlite> ALTER TABLE `users` ADD `uuid` VARCHAR;
```

It can be neither  `NOT NULL` nor `UNIQUE` for now, because every existing record will have it with a `NULL`.

Back to our `UserId` class, let’s make sure it now has `uuid` in its implementation:

```
final class UserId
{
  public function __construct(
    public int $id,
    public ?UuidInterface $uuid,
  ) {}

  public function getId(): int
  {
    return $this->id;
  }

  public function getUuid(): ?UUidInterface
  {
    return $this->uuid;
  }
}
```

It is still nullable because, well, it is null in the database!

Now we need to make sure two things happen:

1. Every existing record will have a non-null uuid; and
1. Every new record will already come with a filled uuid

We consider both are coexisting just fine in the database layer, when we see that at any given moment, `users.uuid` is never `NULL`.

## Step 3 - Make sure every new record has an UUID

Somewhere in your system something stores Users. We need to make sure that everywhere where it happens, the UUID field will be populated.

So given this older implementation:

```
...

public function insert(
  User $user
): void {
  // insert into ...
}

...
```

I’d just patch it with UUID generation and we should be fine:

```
...

public function insert(
  User $user
): void {
  $id = $user->id;

  if ($id->uuid === null) {
    $id->uuid = Uuid::uuid4();
  }

  // insert into ...
}

...
```


I highly recommend you to cover this IF statement with tests, just in case you missed an import or something like that. Apart from that, no other regressions should have been introduced.

Every new record should now have `users.uuid` properly filled.

## Step 4 - Backfill UUID field for older records

This can be done with a script. If you use a migration framework, it will probably be super easy too.

We just need to fetch all users with null uuids and fill them. Something like this would do the trick:

```
$users = getUsersWithEmptyUuid();
foreach ($users as $user) {
  $user->id->uuid = Uuid::uuid4();
  updateUser($user);
}
```

The above is not very representative of every codebase, but I suppose you got the point.

## Step 5 - Make sure everything is up and running

Do not rush to switch the implementations just yet. Let’s make sure the system is up and running, and that `users.uuid` won’t be `NULL` after running the system for a couple of hours more.

Only move to the next step when you are 100% safe that `users.uuid` won’t ever be `NULL` in this table.

## Step 6 - Update UserRepository to use UUID

It seems we’re already in the position to switch to the new UUID implementation. **I don’t recommend blindly switching to the new implementation just yet**.

Better safe than sorry, right? Let’s make sure we protect our code with a feature flag. Let’s update the `SqliteUserRepository` with the following:

```
final class SqliteUserRepository
    implements UserRepository
{
  public function findById(UserId $id): User
  {
    if (
      isFeatureFlagActive('enableNewUsersUuidImplementation')
    ) {
      // New implementation, using Uuid
      $sql = "...";
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute([
        'uuid' => (string) $id->getUuid(),
      ]);
      // ...
    } else {
      // Old implementation, using integer $id
      $sql = "...";
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute([
        'id' => $id->getId(),
      ]);
      // ...
    }
  }
}
```

Long story short: `isFeatureFlagActive()` returns `TRUE` if the feature we requested is enabled and `FALSE` if not. It can be based on configurations, database entries or environment variables. This is not relevant here.

**It is important that you can change the return value of _isFeatureFlagActive()_** **without having to redeploy your code.** This way you can safely rollback to the previous implementation without much friction.

## Step 7 - Deploy, enable and monitor

First deploy it making sure that `isFeatureFlagActive()` will always return `FALSE` so the original implementation is picked up.

Then switch `isFeatureFlagActive()` to return `TRUE`, so the new implementation will be picked up - Again, this could be done via database records, environment variables, SaaS tools or anything you fancy.

**Oh no! Something 's wrong! The website is suddenly super slow!!**

Switch off your feature flag, so `isFeatureFlagActive()` will return `FALSE` again.

...

Things seem to be normal again. Go back to your IDE and try to figure out what happened. Maybe do some clickthrough and debug things to understand what is causing it to be so slow.

Eventually you’ll realise that you did not index the `users.uuid` column, so querying it became super slow because of your ginormous table. Let 's fix it!

## Step 8 - Make UUID unique and index it

As I’m using SQLITE implementation, here’s the snippet that should do the trick:

```
sqlite> CREATE UNIQUE INDEX `users_uuid_uq` ON `users`(`uuid`);
```

Ideally you should also make `users.uuid` NOT NULL, but I’m skipping it because it requires more steps for SQLITE that are not relevant to what I want to demonstrate here.

Alright, things should be fine now. Propagate your changes to production and see how the feature flagged code behaves now.

All good, right? It is time to clean things up.

## Step 9 - Clean up your numeric id

Now that things are deployed and battle tested, it is time to clean up the previous numeric id field.

Whether you’re removing the actual field or just not using it in code is a project decision - and what wasn’t, right?

But eventually your `SqliteUserRepository` would look like this:

```
final class SqliteUserRepository
    implements UserRepository
{
  public function findById(
    UserId $id
  ): User {
    $sql = "...";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      'uuid' => (string) $id->getUuid(),
    ]);
    // ...
  }
}
```

The function that inserted records also deserves some love now. Let’s remove the former IF statement:

```
...

public function insert(
  User $user
): void {
  $user->id->uuid = Uuid::uuid4();

  // insert logic
}

...
```

And if you have decided to remove the numeric id from the database too, let’s make sure the `UserId` code is also cleaned up and remove the `$id` property:

```
final class UserId
{
  public function __construct(
    public UuidInterface $uuid,
  ) {}

  public function getUuid(
  ): UuidInterface {
    return $this->id;
  }
}
```

And because there’s absolutely no reason for UUID to be nullable now, I just removed the question marks from the `$uuid` property too.

## Go grab a snack and reward yourself: your system is safe!

Of course things can differ from project to project, but at the end of the day, you’ll be executing some variation of the described technique.

This applies to pretty much any data-dependent implementation change. Just remember the three phases:

1. Let both implementations coexist
1. Battle test the new implementation
1. Decommission the former implementation

Don’t be shy or ashamed of taking multiple steps. Even if you know you’ll have to delete code afterwards!

Rolling back a deployment or fixing a live database as a reaction to an issue are way more painful than any of the steps I’ve described here.

<div class="align-right">
  --
  <a href="https://twitter.com/nawarian" rel="nofollow">
    @nawarian
  </a>
</div>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "TechArticle",
  "headline": "Safe code migration",
  "description": "I’m writing about a very specific and hairy problem: unique id migration.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/23-safe-code-migration-640.webp"
   ],
  "datePublished": "2021-12-04T00:00:00+08:00",
  "dateModified": "2021-12-04T00:00:00+08:00",
  "author": {
    "@type": "Person",
    "name": "Nawarian Níckolas Da Silva"
  },
   "publisher": {
    "@type": "Organization",
    "name": "ThePHP Website",
    "logo": {
      "@type": "ImageObject",
      "url": "https://thephp.website/favicon.ico"
    }
  }
}
</script>
