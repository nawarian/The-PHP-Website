---
isFeatured: true
slug: php-8-features
title: 'PHP 8.0 released: it looks awesome!'
category: walkthrough
createdAt: 2020-11-26
sitemap:
  lastModified: 2020-11-26
image:
  url: /assets/images/posts/19-php-features-640.webp
  alt: 'An image with a gigantic blue glowing elephant'
tags:
  - core
  - curiosity
  - php8
  - release
meta:
  description:
    PHP 8.0 brings many innovations, among them amazing
    syntax additions, API upgrades,fundamental changes
    to its core and, of course, many bug fixes. Here I
    want to outline some of the main changes to the language!
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/php-8-features/)

It is finally here! As of 26 of November 2020, PHP 8.0 is
released and publicly available for everyone to download.
After 5 release candidates and lots of effort from the
community, we can finally get started with PHP 8.0 on production.

PHP 8.0 brings many innovations, among them amazing syntax
additions, API upgrades,fundamental changes to its core and,
of course, many bug fixes. Here I want to outline some of the
main changes to the language!

But hey, if you don't have sufficient time to read this
article,
[have a quick look at the official release landing page.](https://www.php.net/releases/8.0/en.php) You'll have a nice overview there!

## PHP 8's Syntax changes

There are some cool syntactic additions to the language in
this version! I can clearly see a trend: php is attempting
to be more ergonomic when it comes to quick operations and
to classes.

I list below 8 syntactic changes introduced to PHP 8.0 and
briefly introduce them to you. All of them will have links
to the RFC that introduced them to the language, so in case
you have questions just check it out
[(or open an issue, I'll be glad to answer asap)](https://github.com/nawarian/The-PHP-Website/issues).

### Union Types and the Mixed Type

I wrote a detailed post about how PHP's type system is
organized in scalar, compound and special types.
[You can check it out by clicking this link](/en/issue/php-type-system/).

PHP 8.0 brings two very important changes that make compound
types a concrete language construct instead of convention
as it was before.

[The first change is called Union Types](https://wiki.php.net/rfc/union_types_v2)
, which make it possible to define which types a variable
may hold and will error if unexpected value types are
passed. The syntax is very similar to what TypeScript does:

```php
<?php

function sumTwo(int|float $x): int
{
  return round($x + 2);
}
```

It has some limitations, though. The void type cannot be
used, and all type declarations must not have any ambiguity.
For example `MyClass|object` should not compile, because
object already matches any instance of any class.

[The second change introduces the _mixed_ type to PHP 8.0](https://wiki.php.net/rfc/mixed_type_v2).
The mixed type is actually a very specific union type. You
may think of it as an alias of the Union `array|bool|callable|int|float|null|object|resource|string`
and it should act the same as the type `any` in TypeScript.

### Attributes

This change was definitely the one that brought the biggest
amount of discussion in the PHP community. In total there
were 5 RFCs to compose this single syntactic feature, all
of them discussed in depth by php internals and by the
community in social networks.

The first time it appeared was in 2016,
[when Dmitry proposed](https://wiki.php.net/rfc/attributes)
it but it didn't pass as the implementation wasn't sufficient
to replace user-land implementations as
[Doctrine\Annotations](https://github.com/doctrine/annotations)
or
[Php-Annotations\Php-Annotations](https://github.com/php-annotations/php-annotations).
It was extremely important to build the foundations for
the new attributes syntax RFC.

[In march this proposal was revived by Benjamin Eberlei and Martin Schröder](https://wiki.php.net/rfc/attributes_v2)
, addressing most of the issues the community found before.
The syntax looked like this:

```php
<?php

use PhpAttribute;

<<PhpAttribute>>
class MyAttributesClass
{
}

<<MyAttributesClass>>
function myFunction () {}

$reflection = new ReflectionFunction('myFunction');
// ReflectionAttribute[]
var_dump($reflection->getAttributes());
```

Attributes resolve to a class which can be instantiated from
the _ReflectionAttribute_ object itself. Each attribute
class should itself be annotated with an attribute named
_PhpAttribute_, this changed after the
[attribute amendments RFC](https://wiki.php.net/rfc/attribute_amendments)
passed and now instead of _PhpAttribute_ it should be annotated
with _Attribute_.

This syntax can be used with:
- functions, closures and short closures
- classes, anonymous classes, interfaces, traits
- class constants
- class properties
- class methods
- function or method parameters

This second RFC (attribute amendments) also brought interesting
features such as validating that a certain attribute should be
used with a Class or Function target only, whether it can be
used multiple times and grouping attribute usages.

The last two RFCs (
[this](https://wiki.php.net/rfc/shorter_attribute_syntax
) and [this](https://wiki.php.net/rfc/shorter_attribute_syntax_change)
) were solely about how the attributes usage syntax should look
like. The final syntax looks like this and resembles rust's
attributes syntax:

```php
<?php

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION)]
class MyAttributesClass
{
}

#[MyAttributesClass]
function myFunction () {}

$reflection = new ReflectionFunction('myFunction');
// ReflectionAttribute[]
var_dump($reflection->getAttributes());
```

Notice that `Attribute::TARGET_FUNCTION` over there? It tells
php that this attribute can only be used with functions and
will error if something else decides to use it.

### Nullsafe operator

_Uncaught Error: Call to a member function example() on null_.
This one annoying error keeps chasing many php engineers who
may have forgotten to double check a return type or a mistyped
if condition.

This will change with PHP 8.0, as the
[nullsafe operator was introduced](https://wiki.php.net/rfc/nullsafe_operator).
It performs null checks on calls and short-circuits if some
part of the chain is null, avoiding uncaught errors as the
one mentioned above. The syntax looks like this:

```php
<?php

$obj = new class {
  public function f()
  {
    return null;
  }
}

// "$obj?->" first checks if $obj is null and,
// if not, proceeds with the call
// "f()?->" checks if the return type of f() is null like above
$obj?->f()?->neverCalled();

// neverCalled() was never called, because f() returns null
```

### Non-Capturing Catches

Whenever we write the _catch_ block when handling an exception
we are forced to receive the exception object.

[In PHP 8.0, thanks to Max Semenik](https://wiki.php.net/rfc/non-capturing_catches)
, we are no longer required to do so. Now it is possible to
catch exceptions without caring about the object at all. Like
below:

```php
<?php

try {
  throw new IncredibleException();
} catch (IncredibleException) {
  // I don't care so much about
  // the $exception object
} catch (Exception $e) {
  // But here I do, and that's okay
}
```

### Throw Expression

Previously the _throw_ keyword was just a language statement,
which prevented php engineers from throwing exceptions in some
places where only expressions were expected such as variable
assignments, short closures, ternaries and binary expressions.

[Ilija Tovilo implemented the Throw Expression RFC](https://wiki.php.net/rfc/throw_expression)
which transformed the `throw $obj` statement into an expression.
Meaning that now the following usages are valid:

```php
<?php

$a = null ?? throw new Exception();
$b = $obj->func() || throw new Exception();
$c = fn() => throw new Exception();
```

This feature was inspired by a change introduced to C# in 2017
and a proposal to ECMAScript written in 2018.

### Match Expression

This is my personal favourite one. It intends to bring a cleaner
syntax wherever we'd normally use a _switch_ statement to decide
the value of a variable.

[The RFC was brought by Ilija Tovilo](https://wiki.php.net/rfc/match_expression_v2)
and at this version does not support blocks, so only single
expressions are allowed. The usage looks like this:

```php
<?php

$a = 100;

$twoHundred = match ($a) {
  10, 100, 1000 => $a * 2,
  50, 500, 5000 => $a / 2,
};
```

The above snippet will return `$a * 2` whenever $a equals to 10,
100 or 1000. It would return `$a / 2` if $a equals to 50, 500 or
5000.

It is important to notice that the match syntax builds an
expression, so it can be stored in variables, passed as argument
and be composed with other expressions.

```php
<?php

$type = ...;
$filter = match ($type) {
  'as_object' => $myObject,
  'assoc' => $myObject->toArray(),
} || throw new InvalidArgumentException('Invalid type requested.');
```

Future implementations intend to add support for blocks on the
right-hand of this expression, similar to what Rust does. This
gives the developer more flexibility to write more complex
programs without invading variable scopes.

### Named Parameters

Often we see methods with parameters containing default values
and the only ones we want to change are the last ones. This
forces us to write null for all first entries in order to
modify only the last ones.

Many could argue that this is a smell of bad design, but at
the same time we can't just guarantee great design for every
open source library written out there.

[Nikita Popov then added the Named Parameters feature to PHP 8.0](https://wiki.php.net/rfc/named_params)
, allowing us to skip functions or methods parameters and set
values only to the ones we care about. They must be named for
doing so. Here's how it looks like:

```php
<?php

function myFunc(
  $a = 10,
  $b = 20,
  $c = null
) {
}

myFunc(c: 100);
// $a = 10; $b = 20; $c = 100
```

This also gives us the freedom to detach from the parameter
order previously defined.

I believe that's a great way out for a better looking code
without breaking backwards compatibility with extensions and
libraries out there.

### Constructor Promotion

Some say PHP is verbose as Java when it comes to Object-Oriented
programming. I tend to agree with that and I believe we could
use some cool syntactic sugars that other languages provided over
time.

[The constructor promotion syntactic feature](https://wiki.php.net/rfc/constructor_promotion)
makes it simpler to write classes that receive parameters in
their constructors and immediately assign them to properties.

The following snippet shows what it is capable of:

```php
<?php

class MyClass
{
  public function __construct(public int $x = 0)
  {}
}

// The above is equivalent to this

class MyClass
{
  private int $x;

  public function __construct(int $x = 0)
  {
    $this->x = $x;
  }
}
```

## PHP 8's Core (Virtual Machine) changes

Core changes are normally the ones that can break our code
explicitly or silently, so it is important to pay good
attention to them while upgrading our PHP version.

This version brought some very exciting upgrades to PHP core
both on performance and behaviour. Here I list some of them.

### Just In Time (JIT) Compiler

[I wrote about what JIT is and how it works in PHP](/en/issue/php-8-jit/).
I deeply recommend you to read that post, it will give you
a much better idea about how PHP works internally and which
benefits a Just In Time compiler can bring to the language.

Bottomline is: JIT can increase performance of our php
applications out of the box, can be fine tuned for better
results and paves the way for different PHP applications. 

This won't happen to every php application, though. There
are very specific use cases for JIT and I think the best
you can do is to both [check the RFC](https://wiki.php.net/rfc/jit)
and [the post I've mentioned](/en/issue/php-8-jit/).

One interesting thing about this feature is that it was
implemented before the attributes syntax was approved.
So one of the options available is to JIT Compile only
functions/methods annotated with a `@jit` doc-comment.
This may change in the future to use native `#[jit]`
attributes instead of doc-comments.

### Weak Maps

PHP 7.4 brought us the weak-reference class, which wraps
a reference to an object without preventing it from being
destroyed during runtime.

[A great addition to the language that PHP 8.0 brought are the Weak Maps](https://wiki.php.net/rfc/weak_maps).
Weak Maps use the same concept as Weak References but
implement the _ArrayAccess_, _Countable_ and _Traversable_
interfaces. This results in nicer object bags that won't
prevent objects from being destroyed when all other
references are removed.

I intend to write more about Garbage Collection in PHP in
the future, but if you'd like to know more sooner please
ping me on twitter or open an issue so I can give this
subject priority.

Here's a code sample on how WeakMaps are used:

```php
<?php

$bag = new WeakMap();
$obj = new stdClass();

$bag[$obj] = 42;

// int(1)
var_dump($bag->count());

// delete $obj from memory
// $bag should now be empty
unset($obj);

// int(0)
var_dump($bag->count());
```

### Engine Warnings

This RFC changes how the engine behaves and we should
pay very good attention to it!

Many error messages and severity levels were changed to
be more consistent. No severity levels were downgraded,
only upgraded. Some notices will become warnings and
some warnings will become errors (will throw exceptions).

The full changes list can be found in
[the RFC page](https://wiki.php.net/rfc/engine_warnings)
and I strongly recommend you to read it through as this
kind of issue may pop up silently if you don't have good
enough monitoring in place.

### Magic Methods Signature Checks

This one was written by Gabriel Caruso, whom I was very
lucky to meet this year! He added type checks for magic
methods signature as defined in their documentation.

Every class implementing magic methods that do not
conform with the interface will raise a `FatalError` as
you can check in [the RFC page](https://wiki.php.net/rfc/magic-methods-signature).
Even though this is a breaking change, only 7 repositories
from the top 1000 packagist packages would be affected by it.

### Saner Numeric Strings

PHP can cast numeric strings into actual integers when
necessary. This cast may occur manually or implicitly
depending on which operation you're performing (e.g.
expressions and function calls).

```php
<?php

// int(123)
var_dump((int) "123");
```

More than that, PHP is very forgiving with numeric strings.
Strings like `"2 bananas"` or `"5 apples"` will evaluate to
numbers normally if necessary. More than that, some strings
may be wrongly interpreted as numbers in different situations
(like having a hash with a leading zero, for example).

[The saner numeric strings RFC](https://wiki.php.net/rfc/saner-numeric-strings)
came to solve this issue, by normalizing the way we deal
with numeric strings and raising Type Errors when numeric
types are required but non-numeric strings are passed.

### Numeric Strings Comparison Changes

PHP has two comparison modes: strict (`===`, `!==`) and
non-strict comparison (everything else). Whenever we perform
a non-strict comparison between a string and a number, php
will attempt to cast the string into a number and only then
perform integer comparisons.
[I explain this in detail here](/en/issue/php-type-system/).

Such behaviour created some awkward distortions such as
`0 == "nawarian"` evaluating to true.

[The numeric string comparison RFC](https://wiki.php.net/rfc/string_to_number_comparison)
makes such comparisons a bit saner by inverting the cast
logic: instead of casting the string into a number and
performing a number comparison, PHP 8.0 will cast the
number into a string and perform a string comparison.

A new comparison table was made available and I copy it
straight from the RFC into this page:

Comparison    | Before | After
--------------|--------|------
 0 == "0"     | true   | true
 0 == "0.0"   | true   | true
 0 == "foo"   | true   | false
 0 == ""      | true   | false
42 == "   42" | true   | true
42 == "42foo" | true   | false

## Looking forward for the next years

Of course there are many more things introduced with
PHP 8.0 and I wish I had time and "bock" to write them
down. But from this short list it is already clear that
PHP, the ever dying language, is once again getting
greater and stronger.

I currently know no benchmarks on PHP 8.0 running real
applications that could state this version is faster,
same as before or slower. But I trust that the tools
we were given by the community will enable us even
further to keep developing awesome and blazing fast
applications.

The Just In Time compiler addition rings a very important
bell and should remind us of great tools we aren't paying
as much attention as deserved, in my opinion, such as the
Swoole Extension.

For now let's celebrate this amazing achievement from the
PHP Community and thank all people involved (you're included).
The 8.1 alpha development is already started and I can't wait
to see what comes next!

Please don't forget to share this with your friends and
colleagues, and let me know if you find anything strange
here or would like to add something strange yourself!

Cheers!


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
  "headline": "PHP 8.0 released: it looks awesome!",
  "description": "PHP 8.0 brings many innovations, among them amazing syntax additions, API upgrades,fundamental changes to its core and, of course, many bug fixes. Here I want to outline some of the main changes to the language!",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/19-php-8-features-640.webp"
   ],
  "datePublished": "2020-11-26T00:00:00+08:00",
  "dateModified": "2020-11-26T00:00:00+08:00",
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
