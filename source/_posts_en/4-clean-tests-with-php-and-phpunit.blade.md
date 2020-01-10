---
slug: clean-tests-with-php-and-phpunit
title: Clean tests with PHP and PHPUnit
createdAt: 2020-01-07
sitemap:
  lastModified: 2020-01-07
image:
  url: /assets/images/4-writing-great-tests.jpg
  alt: 'Many developers looking at the same computer screen trying to understand what is going on'
meta:
  description:
    This post aims to help you reducing the number of WTF per second when
    writing, reading and changing test code on your PHP application using
    the test framework PHPUnit.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/voce-achou-que-sabia-sobre-generators/)

There are many tools available in the PHP ecosystem that are ready to provide
great testing experience with php. [PHPUnit is by far the most famous one](https://github.com/sebastianbergmann/phpunit)
, being almost a synonym of testing this language.

The set of good practices for testing are not much shared, though. There
are many opinions on why and when to write tests, what type of tests to
write and so on. But honestly **it doesn't make sense to write any test
if you won't be able to read it later**.

## Tests are a very special type of documentation

As [I wrote previously on TDD with PHP](/en/issue/real-life-tdd-php/),
a test will (or at least should) always make clear what a certain piece of code
aims to achieve as a goal.

**If one test can't express an idea, it is a bad test.**

With this in mind, I prepared a set of practices that might support
php developers on writing good, readable and useful tests.

## Starting with the basics

There's a set of common practices that many people follow without questioning
themselves "why". I'll list them below without providing much explanation so
you can always use this as a checklist for your tests.

### Tests should have no I/O operations

**Main reasoning**: I/O is slow and unreliable.

**Slow:** Even with the best equipment on earth, I/O is still slower
than memory access. **Tests should alwyas run fast**, otherwise people
won't run them often enough.

**Unreliable:** a certain file, binary, socket, folder, dns record might not be
available on all machines your code will be tested against. **The more
you depend on I/O while testing, the more you test is bound to the
infrastructure**.

Operations considered I/O:
- File reading/writing
- Network calls
- External process calls (using exec, proc_open...)

There are cases where having I/O will make it faster to write a test.
**But be aware**: making sure such operations work the same in your
development, build and deployment machines can be a big headache.

**Isolating tests so they don't require I/O:** below I present one
design decision that can be made to prevent your tests from performing
I/O operations by **segregating responsibilities to interfaces**.

Here's an example:

```php
public function getPeople(): array
{
  $rawPeople = file_get_contents(
    'people.json'
  ) ?? '[]';

  return json_decode(
    $rawPeople,
    true
  );
}
```

The moment we start testing this method, we'll be forced to create
a local file for testing, and from time to time, keep a snapshot
of this file. Like the following:

```php
public function testGetPeopleReturnsPeopleList(): void
{
  $people = $this->peopleService
    ->getPeople();

  // assert it contains people
}
```

For such test, we'd need to set up preconditions for the test to run.
While this might make sense on a first sight, it is actually terrible.

**Skipping a test because a precondition is not met won't assure
quality on our software. It just hides possible bugs!**

**Fixing it:** isoalte the I/O operations by segregating this
responsibility to an interface.

```php
// extract the fetching
// logic to a specialized
// interface
interface PeopleProvider
{
  public function getPeople(): array;
}

// create a concrete implementation
class JsonFilePeopleProvider
  implements PeopleProvider
{
  private const PEOPLE_JSON =
    'people.json';

  public function getPeople(): array
  {
    $rawPeople = file_get_contents(
      self::PEOPLE_JSON
    ) ?? '[]';

    return json_decode(
      $rawPeople,
      true
    );
  }
}

class PeopleService
{
  // inject via __construct()
  private PeopleProvider $peopleProvider;

  public function getPeople(): array
  {
    return $this->peopleProvider
      ->getPeople();
  }
}
```

I know, so now `JsonFilePeopleProvider` uses I/O anyways. True.

Instead of `file_get_contents()` we can use an abstraction layer
like the [Flysystem's Filesystem](https://flysystem.thephpleague.com/docs/adapter/local/)
which can be easily mocked.

And what's the point of having `PeopleService` then? Good question.
That's what tests are for: question your design, kill useless code.

### Tests should be concise and meaningful

**Main reasoning:** tests are a form of documentation. Keep them clean,
short and readable.

**Clean and short:** no clutter, no thousand lines of mocking, no
sequence of assertions.

**Readable:** tests should tell a story. The "Given, When, Then"
structure is amazing for this.

Here are some characteristics of a nice and readable test:
- It contains only necessary "assert" method calls (preferably only
one)
- It tells you very clearly what should happen given a condition
- It tests only one path of execution of a method

**It is important to notice** that if your implementation contains
if conditions, switch statements or loops, **they should all be
explicitly covered with tests.** So early returns will always contain
a test, for example.

Again: is not about coverage, is about documenting.

Let me show you an example of confusing test:

```php
public function testCanFly(): void
{
  $noWings = new Person(0);
  $this->assertEquals(
    false,
    $noWings->canFly()
  );

  $singleWing = new Person(1);
  $this->assertTrue(
    !$singleWing->canFly()
  );

  $twoWings = new Person(2);
  $this->assertTrue(
    $twoWings->canFly()
  );
}
```

Let's adopt the "Given, When, Then" format here and see how it changes:

```php
public function testCanFly(): void
{
  // Given
  $person = $this->givenAPersonHasNoWings();

  // Then
  $this->assertEquals(
    false,
    $person->canFly()
  );

  // Further cases...
}

private function givenAPersonHasNoWings(): Person
{
  return new Person(0);
}
```

Just like the "Given" clause, the whens and thens can be extracted
to private methods as well. Whatever makes your test more readable.

Now, that assertEquals is full of clutter with little meaning. A human
reading this has to parse the assertion to understand what it should mean.

```php
// ...
$person = $this->givenAPersonHasNoWings();

$this->assertFalse(
  $person->canFly()
);

// Further cases...
```

Now, this "Further cases" appearing twice on our text is already a great
clue this test is doing too many assertions. Meanwhile "testCanFly()" doesn't
mean anything useful at all.

Let's make this test case great again:

```php
public function testCanFlyIsFalsyWhenPersonHasNoWings(): void
{
  $person = $this->givenAPersonHasNoWings();
  $this->assertFalse(
    $person->canFly()
  );
}

public function testCanFlyIsTruthyWhenPersonHasTwoWings(): void
{
  $person = $this->givenAPersonHasTwoWings();
  $this->assertTrue(
    $person->canFly()
  );
}

// ...
```

We could even rename the test method to match a real-life scenario like
`testPersonCantFlyWithoutWings`, but that's for me good enough.

### A test should not depend on another

**Main reasoning:** a test should be able to run and succeed in any order.

So far I can't find a good reason for coupling tests.

Recently I got asked about a Logged-in feature test and I'll take it as a
good example here.

The test would perform the following:
- Generate a logged-in JWT token
- Execute a logged-in feature
- Assert state changes

The way it was set up was the following:

```php
public function testGenerateJWTToken(): void
{
  // ... $token
  $this->token = $token;
}

// @depends testGenerateJWTToken
public function testExecuteAnAmazingFeature(): void
{
  // Execute using $this->token
}

// @depends testExecuteAnAmazingFeature
public function testStateIsBlah(): void
{
  // Poll for state changes on
  // Logged-in interface
}
```

This is bad for a couple of reasons:
- PHPUnit won't guarantee order of execution like this
- A test should be able to run independently
- Parallel tests might break randomly

The simplest way to overcome this I can think of is, again,
the "Given, When, Then". This way we make the test more concise
and tell a story by showing its dependencies in a clear way that
explains the feature itsef.

```php
public function testAmazingFeatureChangesState(): void
{
  $token = $this->givenImAuthenticated();
  $this->whenIExecuteMyAmazingFeature(
    $token
  );
  $newState = $this->pollStateFromInterface(
    $token
  );

  $this->assertEquals(
    'my-state',
    $newState
  );
}
```

We would also need to add tests for authenticating and so on.
This is structure is so good that
[Behat enforces it by default](https://behat.org/en/latest/quick_start.html).

### Always inject dependencies

**Main reasoning:** mocking global state is bad, so please don't.

This is a lesson for life. Forget about static classes (that keep state) and
singleton instances. If your class depends on something, make it injectable.

Here's a particularly sad example:
```php
class FeatureToggle
{
  public function isActive(
    Id $feature
  ): bool {
    $cookieName = $feature->getCookieName();

    // Early return if cookie
    // override is present
    if (Cookies::exists(
      $cookieName
    )) {
      return Cookies::get(
        $cookieName
      );
    }

    // Evaluate feature toggle...
  }
}
```

Now. How can you test this early return?

That's right. You can't.

In order to test this, we would need to understand the behaviour of
this `Cookies` class and make sure we can reproduce the whole environment
behind it so we can force some returns there.

Please don't.

We can fix this by injecting `Cookies` as dependency. Our test would look
like the following:

```php
// Test class...
private Cookies $cookieMock;

private FeatureToggle $service;

// Preparing our service and dependencies
public function setUp(): void
{
  $this->cookieMock = $this->prophesize(
    Cookies::class
  );

  $this->service = new FeatureToggle(
    $this->cookieMock->reveal()
  );
}

public function testIsActiveIsOverriddenByCookies(): void
{
  $feature = $this->givenFeatureXExists();
  $this->givenCookieOverridesFeatureWithTrue(
    $feature
  );

  $this->assertTrue(
    $this->service->isActive($feature)
  );
  // additionally we can assert
  // no other methods were called
}

private function givenCookieOverridesFeatureWithTrue(
  Id $feature
): void {
  $cookieName = $feature->getCookieName();
  $this->cookieMock->exists($cookieName)
    ->shouldBeCalledOnce()
    ->willReturn(true);

  $this->cookieMock->get($cookieName)
    ->shouldBeCalledOnce()
    ->willReturn(true);
}
```

Same happens with singletons. If you want to make an object a singleton,
make sure you configure your dependency injector properly instead of
using the Singleton (anti) pattern.

Otherwise you will end up writing methods that are only useful for test
cases like `reset()` or `setInstance()` which is completely insane.

