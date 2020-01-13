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

[Leia em Português](/br/edicao/testes-legiveis-com-php-e-phpunit/)

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
themselves "why". I'll list many of them while trying to explain at least
a bit the reasoning behind each.

### Tests should have no I/O operations

**Main reasoning**: I/O is slow and unreliable.

**Slow:** Even with the best equipment on earth, I/O is still slower
than memory access. **Tests should always run fast**, otherwise people
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
a local file for testing and, from time to time, keep a snapshot
of this file. Like the following:

```php
public function testGetPeopleReturnsPeopleList(): void
{
  $people = $this->peopleService
    ->getPeople();

  // assert it contains people
}
```

For such test, we'd need to **set up preconditions** for the test to run.
While this might make sense on a first sight, it is actually **terrible**.

**Skipping a test because a precondition is not met won't assure
quality on our software. It will only hide bugs!**

**Fixing it:** isolate I/O operations by moving this responsibility to an
interface.

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
That's also **what tests are for: question your design, kill useless
code.**

---
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
- It won't mock the whole universe to assert something

**It is important to notice** that if your implementation contains
if conditions, switch statements or loops, **they should all be
explicitly covered with tests.** So early returns will always contain
a test, for example.

Again: **this is not about coverage, is about documenting.**

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

**Using specific assertions will make your test much more readable.**
`assertTrue()` should receive a variable containing a boolean, never
an expression like `canFly() !== true`.

So from previous example, we replace the `assertEquals` between `false`
and `$person->canFly()` with a simple `assertFalse`:

```php
// ...
$person = $this->givenAPersonHasNoWings();

$this->assertFalse(
  $person->canFly()
);

// Further cases...
```

Crystal clear! Given a person has no wings, it shouldn't be able to fly!
Reads like a poem 😍

Now, this "Further cases" appearing twice on our text is already a great
clue this test is doing too many assertions. Meanwhile the method name
`testCanFly()` doesn't mean something useful at all.

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

---
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
  // Given
  $token = $this->givenImAuthenticated();
  
  // When
  $this->whenIExecuteMyAmazingFeature(
    $token
  );
  $newState = $this->pollStateFromInterface(
    $token
  );

  // Then
  $this->assertEquals(
    'my-state',
    $newState
  );
}
```

We would also need to add tests for authenticating and so on.
This is structure is so good that
[Behat enforces it by default](https://behat.org/en/latest/quick_start.html).

---
### Always inject dependencies

**Main reasoning:** mocking global state is terrible, not being able
to mock dependencies at all makes it impossible to test a feature.

Here is a lesson for life: **Forget about stateful static classes and
singleton instances.** If your class depends on something, make it
injectable.

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

To test this we would need to understand the behaviour of
this `Cookies` class and make sure we can reproduce the whole environment
behind it so we can force some returns there.

Please don't.

We can fix this by injecting an instance of `Cookies` as dependency.
Our test would look like the following:

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
  // Given
  $feature = $this->givenFeatureXExists();

  // When
  $this->whenCookieOverridesFeatureWithTrue(
    $feature
  );

  // Then
  $this->assertTrue(
    $this->service->isActive($feature)
  );
  // additionally we can assert
  // no other methods were called
}

private function givenFeatureXExists(): Id
{
  // ...
  return $feature;
}

private function whenCookieOverridesFeatureWithTrue(
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

**Same occurs with singletons.** So if you want **to make an object unique**,
make sure you **configure your dependency injector properly** instead of
using the Singleton (anti) pattern.

Otherwise you will end up writing methods that are only useful for test
cases like `reset()` or `setInstance()`. Sounds insane to me.

Changing your design to make testing easier is fine! **Creating methods
to make testing easier is not fine.**

---
### Never test protected/private methods

**Main reasoning:** They way we test features is by asserting how their
signature behave: given this conditions, when I input X, I expect Y to occur.
**Private/Protected methods are not part of the feature's signature.**

I even refuse to show you a way to "test" private methods, but here goes a
hint: you can only do this by using [the reflection API](https://www.php.net/manual/en/book.reflection.php).

Always punish yourself somehow whenever you think of using reflection to
test a private method! Bad baad dev!

By definition, private methods will only be called from inside. So they
are not publicly accessible. This means, only `public` methods in this
same class can invoke such methods.

**If you tested all your public methods, you also tested all your
protected/private ones.** If not, feel free to delete your protected/private
methods, nobody is using them anyways.

---
## Beyond the basics: the interesting stuff

I hope you didn't get bored so far. Basics are basics, but they need to be
stated.

Now, within the next lines I'll share with you some opinions I
carry about writing clean tests and each decision impacts on my development
workflow.

**I'd say the most important values I keep in mind while writing tests are:**
- Learning
- Receiving quick feedback
- Documenting
- Refactoring
- Design while testing

Such opinions share at least one of such values and each of them support
the others.

### Tests come first, not after

**Values**: learning, receiving quick feedback, documenting, refactoring,
design while testing.

This one is the basis of everything. It is so important, it carries all
values at once.

Writing test first forces you to first understand how your "given,
when, then" should be structured. **You document first by doing so** and,
most importantly, **learn and state your requirements** as the most
important things.

**Sounds weird to write a test before implementing anything?** Imagine how
awkward it is to implement something and while testing, discover that
all the "given, when, then" statements don't make any sense at all.

It also allows you to run your expectations every 2 seconds or so. **You'll
receive feedback the quickest way possible. No matter how big or small a
feature might look like.**

**Green tests are the perfect field for refactoring.** At some point I'll
probably write about refactoring, but main thing is: no tests, no refactoring.
Because refactoring with no tests is simply too dangerous.

And last but not least, by setting your "given, when, then" it becomes clear
what interface your methods should have and how they should behave. **Keeping
this test clean will also force you to constantly take different design
decisions.**

It will force you to create factories, interfaces, break inheritances and so
on. And yes, to make testing easier!

If your tests are a live document aiming to explain how your software works,
**it is extremely important they explain it clearly.**

---
### No tests is better than bad tests

**Values**: learning, documenting, refactoring.

Many developers think of tests the following way: they write a feature,
punch their testing framework until tests cover a certain amount
of new lines and push to production.

What I wish we'd take more into consideration, though, is when the next
developer comes to this feature. **What tests are really telling this person...**

Often tests are messy when names don't tell much. What is clearer when it comes
to test names: `testCanFly` or `testCanFlyReturnsFalseWhenPersonHasNoWings`?

Whenever your tests represent nothing more than clutter and code forcing
the framework to cover more lines with examples that don't seem to make sense
at all, it is time to stop and think whether it make sense to even write this test.

Even very silly things like naming variables with `$a` and `$b`, or giving names
that don't relate to the use case at all.

**Remember:** your tests are a live document, attempting to explain how your
software should behave. `assertFalse($a->canFly())` is not documenting much.
`assertFalse($personWithNoWings->canFly())` is.

---
### Run your tests compulsively

**Values**: learning, receiving quick feedback, refactoring.

**Before you start any feature: run tests.** If tests are broken before you touched
anything, you'll know _before_ you wrote any code and you won't spend precious
minutes debugging broken tests you weren't even aware of.

**After saving a file: run tests.** The sooner you know you broke something, the
sooner you'll fix the issue and move forward. If interrupting your flow to fix
an issue before moving forward sounds unproductive, imagine coming many steps
back to fix an issue you didn't even know you caused.

**After chatting with your colleague for five minutes or checking github
notifications: run tests.** If tests are red, you know where you stopped. If tests
are green, you know you can move forward.

**After refactoring something, even variable names: run tests.**

Just really, run the freaking tests. As often as you'd hit the "Save" hotkey.

In fact, [PHPUnit Watcher](https://github.com/spatie/phpunit-watcher)
does exactly this for you and even sends desktop notifications!

---
### Big tests, big responsibilities

**Values**: learning, refactoring, design while testing.

Ideally each class would have one test counterpart for itself. Also
each public method in this class, should be covered with tests. And every
if condition or switch statement...

Counts are more or less like this:

- 1 class = 1 test case
- 1 method = 1 or more tests
- 1 alternative path (if/switch/try-catch/exception) = 1 test

So a simple code like this would generate 4 different tests:

```php
// class Person
public function eatSlice(Pizza $pizza): void
{
  // test exception
  if ([] === $pizza->slices()) {
    throw new LogicException('...');
  }
  
  // test exception
  if (true === $this->isFull()) {
    throw new LogicException('...');
  }

  // test default path (slices = 1)
  $slices = 1;
  // test alternative path (slices = 2)
  if (true === $this->isVeryHungry()) {
    $slices = 2;
  }

  $pizza->removeSlices($slices);
}
```

**As you grow in public methods count, your tests will also grow in number.**

And nobody likes reading large documents. As your test case is also a document,
leaving it small and concise will only increase its quality and usefulness.

This is also a big sign that your class is accumulating responsibilities and
might be time to put on your refactoring hat to remove features, move to
different classes or rethink part of your design.

---
### Keep a regression suite

**Values**: learning, documenting, receiving quick feedback.

Take the following function:

```php
function findById(string $id): object
{
  return fromDb((int) $id);
}
```

You expected someone to pass `"10"` but instead, passed `"10 bananas"`. Both
retrieve the value, one should not. You have a bug.

First thing you do? Write a test to state this behaviour is wrong!!

```php
public function testFindByIdAcceptsOnlyNumericIds(): void
{
  $this->expectException(InvalidArgumentException::class);
  $this->expectExceptionMessage(
    'Only numeric IDs are allowed.'
  );

  findById("10 bananas");
}
```

Tests are not passing, of course. But now you know what to do to make them pass.
Remove the bug, make tests green, push, deploy and be happy.

Keep this test there, forever. If possible, to a test suite specialized on
regression and link it to an issue.

There you go! Quick feedback bug fixing, documentation, regression proof
code and happiness.
