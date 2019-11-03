---
slug: real-life-tdd-php
title: Real Life Test-Driven Development with PHP
createdAt: 2019-11-03
sitemap:
  lastModified: 2019-11-03
meta:
  description:
    Everyone loves talking about Test-Driven development, but
    very few cases are actually showing how to apply it. This post
    brings an implementation example with PHP, PHPUnit.
  twitter:
    card: summary
    site: '@nawarian'
---

**Before you start**

TDD has many techniques to be used, this post presents a couple of them.
If you seek deeper knowledge on Test-Driven Development, there's a book
for you: **_"Test Driven Development: By Example"_, by Kent Beck**.

All code written here is available on [thephp.website's github repository]({{ $page->get('github') . '/tree/master/code/1-real-life-tdd-with-php/archive-org-client' }}).

# Real Life Test-Driven Development with PHP

**No BS mode: ON.** (let's move fast!)

Test Driven Development is not about writing unit tests, **it is about
testing first**.

Tests are not the most important thing, **we write them to have quick
and constant feedback** during development.

Being that said: our development cycle looks like the following:
1. [Write a high-level test, run and see it fail](#1-set-up-the-testing-environment)
1. [Make this test succeed the dumbest way possible](#2-make-this-test-succeed-the-dumbest-way-possible)
1. [Refactor the dumb implementation until is no longer dumb](#3-refactor-the-dumb-implementation-until-is-no-longer-dumb)

## Before "how", comes "why"

There are a couple of great reasons to write tests first. Be aware of
them so you understand why keep such practices.

Writing test first:
- forces you to know what you want to achieve before you start coding
- keeps you focused on your goal
- engages you into a constant feedback cycle: change, save, run test

# Building a metadata adapter for Archive.org with TDD

To come up with a reasonable coding example, let's build a client
to fetch metadata from Archive.org's items.

**What we know:**

- Archive.org allows uploading files and call them "Item"
- [Here's an example of Item named "nawarian-test"](https://archive.org/details/nawarian-test)
- An item contains multiple files, representing the file in multiple
forms and its metadata
- Every item contains metadata like creation date, name, files...
- Archive.org provides an API to fetch metadata with the following
URL pattern: `https://archive.org/metadata/<item-name>`

**What we want:**

A class to query an item's metadata on Archive.org and respond
with a custom entity class named `Nawarian\ArchiveOrg\Item\Metadata`.

Let's then build our basic setup and write our test to guarantee
we'll achieve what we want.

## 1. Set up the testing environment

Very quickly: let's create a new folder for our project and install
all required packages, get tests up and running. My setup usually
comes with PHPUnit and Mockery:

```shell script
$ mkdir archive-org-client/ && cd archive-org-client
$ composer require phpunit/phpunit mockery/mockery
$ ./vendor/bin/phpunit --generate-configuration
``` 

While generating phpunit config you'll be asked about tests dir and
other things. Pick the default one for every prompt (simply press enter).

The default set up expect us to write our tests under a `tests` folder,
and our code under `src`. Let's create them:

```shell script
$ mkdir tests src
```

We also need to configure composer's **autoloader**. Update `composer.json`
so it looks like the following:

File: **composer.json**
```json
{
    "require": {
        "phpunit/phpunit": "^8.4",
        "mockery/mockery": "^1.2"
    },
    "autoload": {
        "psr-4": {
            "Nawarian\\ArchiveOrg\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Nawarian\\ArchiveOrg\\Test\\": "tests/"
        }
    }
}
```

With the new `composer.json` in place, let's generate the autoloader again:
```shell script
$ composer dump-autoload
```

We can now create our test class and start moving!

File: **tests/ClientTest.php**
```php
<?php

namespace Nawarian\ArchiveOrg\Test;

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testMyTest(): void
    {
        $this->assertTrue(true);
    }
}
```

And make sure phpunit can run our test normally:
```shell script
$ ./vendor/bin/phpunit -c phpunit.xml
```

Well done! With our test set up in hands, let's move to our
first step on tdd.

<h1 id="1-set-up-the-testing-environment">
    1. Write a high-level test
</h1>

Our goal, again: A class to query an item's metadata on Archive.org
and respond with a custom entity class named
`Nawarian\ArchiveOrg\Item\Metadata`. 

Our test must look like the following:

File: **tests/ClientTest.php**
```php
<?php

namespace Nawarian\ArchiveOrg\Test;

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testClientFetchesMetadata(): void
    {
        $client = new \Nawarian\ArchiveOrg\Client();

        $metadata = $client->fetchMetadata('nawarian-test');

        $this->assertSame('nawarian-test', $metadata->identifier());
        $this->assertSame('2019-02-19 20:00:38', $metadata->publicDate());
        $this->assertSame('opensource', $metadata->collection());
    }
}
```

That's it! We need a `Client` that contains a `fetchMetadata()` method,
that receives an `identifier` string (nawarian-test in our case). We
also want this metadata to be an object with `identifier()`, `publicDate()`
and `collection()` methods, returning the values available on the API.

**Save, run phpunit and see the test failing.**

<h1 id="2-make-this-test-succeed-the-dumbest-way-possible">
    2. Make this test succeed the dumbest way possible
</h1>

First error we see says `Class 'Nawarian\ArchiveOrg\Client' not found`.
Fixing it is simple, create a class matching this FQN. Let's do it under
`src/`.

File: **src/Client.php**
```php
<?php

namespace Nawarian\ArchiveOrg;

class Client
{
}
```

Save, run phpunit. Next error says `Call to undefined method Nawarian\ArchiveOrg\Client::fetchMetadata()`.
Even easier, just add the method to the `Client` class:

```php
public function fetchMetadata(string $identifier): object
{
    return new \stdClass();
}
```

Save, run phpunit. Next error says `Call to undefined method stdClass::identifier()`.
Let's then use anonymous classes to quickly remove these errors
from our screen!

```php
public function fetchMetadata(string $identifier): object
{
    return new class {
        public function identifier(): string
        {
            return '';
        }

        public function publicDate(): string
        {
            return '';
        }

        public function collection(): string
        {
            return '';
        }
    };
}
```

What's missing now is to make our test pass **the dumbest way possible**.
I can only think of hard-coding the values to match our assertions:

```php
public function fetchMetadata(string $identifier): object
{
    return new class {
        public function identifier(): string
        {
            return 'nawarian-test';
        }

        public function publicDate(): string
        {
            return '2019-02-19 20:00:38';
        }

        public function collection(): string
        {
            return 'opensource';
        }
    };
}
```

Awesome! Tests are passing! Time to make the implementation real, so we
can fetch metadata from the API itself. **From this moment we start
our feedback loop during development.**

<h1 id="3-refactor-the-dumb-implementation-until-is-no-longer-dumb">
    3. Refactor the dumb implementation UNTIL is no longer dumb
</h1>

The **until** word here is extremely important. This is the last, but
repeatable step.

This means that we keep coming back to it until we're happy with the
implementation.

**3.1 Introducing the `Item\Metadata` class**

First refactoring I feel like is to come up with our `Metadata` class,
this way we can remove that nasty `return new class {};`. To it:

File: **src/Item/Metadata.php** (methods copied from Client's anonymous class)
```php
<?php

namespace Nawarian\ArchiveOrg\Item;

class Metadata
{
    public function identifier(): string
    {
        return 'nawarian-test';
    }

    public function publicDate(): string
    {
        return '2019-02-19 20:00:38';
    }

    public function collection(): string
    {
        return 'opensource';
    }
}
```

Update `Client::fetchMetadata()` implementation right away. Notice how
return type also changed to `Metadata`.

File: **src/Client.php**
```php
// ...

use Nawarian\ArchiveOrg\Item\Metadata;

// class Client...

public function fetchMetadata(string $identifier): Metadata
{
    return new Metadata();
}
```

Save, run phpunit. Tests are still passing. We're doing great!

**3.2  Add requested information to `Metadata`'s constructor**

Instead of hard coding our result to `Metadata`'s file, let's delegate
the data passing responsibility to the `Client` class and receive data
from `Metadata`'s constructor:

File: **src/Item/Metadata.php**
```php
class Metadata
{
    private $identifier;

    private $publicDate;

    private $collection;

    public function __construct(string $identifier, string $publicDate, string $collection)
    {
        $this->identifier = $identifier;
        $this->publicDate = $publicDate;
        $this->collection = $collection;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function publicDate(): string
    {
        return $this->publicDate;
    }

    public function collection(): string
    {
        return $this->collection;
    }
}
```

And now we delegate data passing to `Client`.

File: **src/Client.php**

```php
public function fetchMetadata(string $identifier): Metadata
{
    return new Metadata('nawarian-test', '2019-02-19 20:00:38', 'opensource');
}
```

Save, run phpunit. Everything is still green. Move on!

**3.3 Call the API to fetch actual data**

`Client` is still providing fake data, which is not really cool.
Let's hit the archive.org's API to fetch the data we need.

Remember the endpoint is `https://archive.org/metadata/<identifier>`. So by
calling `Client::fetchMetadata()` passing `nawarian-test` as identifier (test
is already doing this), we should call `https://archive.org/metadata/nawarian-test`.

I'll quickly do this by using `file_get_contents()`:

File: **src/Client.php**
```php
public function fetchMetadata(string $identifier): object
{
    $jsonData = file_get_contents("https://archive.org/metadata/{$identifier}");
    $decoded = json_decode($jsonData, true);
    $metadata = $decoded['metadata'];

    return new Metadata(
        $metadata['identifier'],
        $metadata['publicdate'],
        $metadata['collection']
    );
}
```

Save, run phpunit. Tests are passing. We achieved our goal.

## Keep refactoring or call it a day

The main idea of the loop described on step 3.3 is to implement towards
a very well defined goal.

You'll face many "aargh" moments, and will want to implement the best way
possible right in the beginning. **Don't fall into this trap!**

The longer you stay without feedback (without seeing test results), the bigger
your chances to make a breaking changes without realizing.

Whenever you want to do something you feel is very important, note it down
and keep moving forward! Keep it as the next item in your refactoring loop,
but don't stop your current iteration.

I can name a few things I'd like to do with the current implementation we have:

- I'd like to have a PSR-18 compatible http client and remove the
`file_get_contents()` call
- I'd like to split our test into unit and integration
- I'd like to have a better hydration for `Metadata` class

Also important to notice we didn't test any exception case. Those should
be part of your implementation as well! How should the program behave
when `identifier` doesn't exist?

The more you code, the more you'll want to code. Your job here is to
understand when you should stop and move on to next topic.

**Just never forget to keep the feedback loop going: refactor, save,
run phpunit.**

That's it. No need to wait for implementing TDD any longer.

Keep rocking, read Kent Beck's book and feel free to reach me out
for questions or complaints.

<div class="align-right">
  --
  <a href="https://twitter.com/nawarian">
    @nawarian
  </a>
</div>
