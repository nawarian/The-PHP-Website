---
slug: php-type-system
title: Everything you need (and don't need) to know about PHP's type system
category: walkthrough
createdAt: 2020-07-25
sitemap:
  lastModified: 2020-07-25
image:
  url: /assets/images/posts/17-php-type-system-640.webp
  alt: 'A woman holding a book about cruel texts.'
tags:
  - core
  - curiosity
  - php8
meta:
  description:
    This is the absolute best guide you'll find in the internet
    about how php handles typing internally and in userland.
  twitter:
    card: summary
    site: '@nawarian'
---

**PHP is a dynamically typed scripting language** and until the year
of 2015 php had no support for statically declared types at all. One
could cast to scalar types explicitly in the code, but declaring scalar
types in methods and functions signatures wasn't a thing until PHP 7.0
with the [Scalar Type Declarations](https://wiki.php.net/rfc/scalar_type_hints_v5)
and [Return Type Declarations](https://wiki.php.net/rfc/return_types) rfcs.

This doesn't mean that from version 7.0 PHP became a statically typed
language, though. **It has type hinting that can be analyzed statically**
but **it still supports dynamic types** and even allows them to be mixed.
See the example below:

```php
<?php

function returnsInt(): int
{
  return '100';
}
```

No doubt that **there's a type mismatch there**. The return type is
supposed to be _int_ and the returned type is in fact a _string_. Now,
what PHP does there is to automatically transform the token '100'
into an integer in order to return the required type. Even though it
seems to have an extra cost, it doesn't. PHP's type juggling is _nearly_
cost-free in many cases.

To better clarify how the language deals with types I'll split this article
into the following sections:

* [Kinds of types in php](#kinds-of-types-in-php)
* [Type "operations" in php](#type-operations-in-php)
* [Union Types](#union-types)
* [PHP's type juggling](#php-type-juggling)
* [PHP type modes](#php-type-modes)

If you have suggestions on what to add here, feel free to
[reach out to me on twitter](https://twitter.com/nawarian) or opening an issue on
github.

**Aahh!! If you like this kind of content, you'll certainly enjoy my post about
[How the Just In Time compiler works](/en/issue/php-8-jit/). Just open in another
tab and check it out later, you won't regret 😉**

<hr />

<h2 id="kinds-of-types-in-php">Kinds of types in php</h2>

PHP's type system is very simplified when it comes to language features.
For example there's no _char_ type, or _unsigned_ types or even _int8_,
_int16_, _int32_, _int64_...

The _char_ type is simplified to a string type and all _integer_ variations
are simplified into an _integer_ type. Whether that's a good or bad thing, is
up to you.

One can always inspect a variable's type using the 
[gettype()](https://www.php.net/manual/en/function.gettype) function or using
the [var_dump()](https://www.php.net/manual/en/function.var-dump) function and
checking its output.

PHP comes with three different kinds of types: **scalar types**, **compound types**
and **special types**.

### Scalar types

Scalar types are the bare bones of the language and they are four:

* Boolean (`bool` | `boolean`)
* Integer (`int` | `integer`)
* Float (`float` | `double`)
* String (`string`)

By definition scalar types do not carry behaviour or state with themselves.
Expressions like `100->toString()` or `'thephp.website'::length()` are invalid.

**Main takeaway: scalar values do not have behaviour or state, they just represent
a value.**

### Compound types

Compound types are much more interesting because even though they are very similar
to the scalar type, **each one of the four compound types carry different syntactic
capabilities.**

The four compound types are:

* [array](#compound-type-array)
* [object](#compound-type-object)
* [callable](#compound-type-callable)
* [iterable](#compound-type-iterable)

<h4 id="compound-type-array">The array compound type</h4>

An array is in fact a hashmap, built-in to the language. Meaning that it stores
values in a **key => value** manner. Even if you use it purely as a vector.

Arrays are very flexible structures when it comes to size, internal types and
key-value mapping. The examples below are all valid arrays:

```php
<?php

$vec = [0, 1, 2];
// $vec[1] is int(1)

$map = ['a' => 1, 'b' => 2];
// $map['a'] is int(1)

$map_ish = ['a' => 1, 0 => 2];
// $map_ish['a'] is int(1)
// $map_ish[0] => is int(2)
```

Unlike C, php won't require you to define an array's size before creating it.
This of course comes with a memory consumption cost: the bigger your array size,
the more memory you'll consume in crazy proportions (in fact, arrays are allocated in powers of two).
How this consumption works is out of the scope of this article,
[feel free to ping me if you'd like to hear more](https://twitter.com/nawarian).

In case you're curious about this statement, the video below presents some charts
and further insights on arrays vs. object memory profiles.

<iframe style="margin: auto; margin-bottom: 20px;" width="560" height="315" src="https://www.youtube.com/embed/JBWgvUrb-q8?start=1000" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

As you'll see bellow, arrays are also considered to be of type _iterable_, meaning
that you can iterate over them using a _foreach_ loop. But they also provide
[specific functions that can manipulate their internal pointers](https://www.php.net/manual/en/ref.array.php).

**Main takeaway: array is an extremely flexible compound type that can be perceived
as a HashMap and is also considered an iterable type.**

<h4 id="compound-type-object">The object compound type</h4>

Due to php's architecture, the _object_ compound type normally has a much lower memory
consumption profile when compared with arrays. That's because _normally_ one would use
the object type by creating instances of classes.

Objects can carry state and behaviour. Meaning that php will offer dereferencing
language structs to access an object's internals. The snippet below illustrates
a php object's derefs:

```php
<?php

class MyClass
{
  private const A = 1;
  public int $property = 0;
  public function method(): void {}
}

$obj = new MyClass();
// $obj is object(MyClass)
// $obj::A is int(1)
// $obj->property is int(0)
// $obj->method() is null
```

An object can also be normally created as a result of a type cast from an array.
Transforming an array's keys into objects property names. Such cast will always
result in an `object(stdClass)` type.

```php
<?php

$obj = (object) ['a' => 1];
// $obj is object(stdClass)
// $obj->a is int(1)
```

Important to notice that casting an array with numeric keys into an object is valid,
but one can't dereference its value because property names may not start with numbers.

```php
<?php

$obj = (object) [0, 1]; // Legal
$obj->0; // Illegal
```

**Main takeaway: objects normally have lower memory profiles than arrays, they carry
state and behaviour, they all inherit from stdClass and can be created by casting an
array.**

<h4 id="compound-type-callable">The callable compound type</h4>

A callable in php is anything that can be called (oh don't you say!!) with parenthesis
or with the [call_user_func()](https://www.php.net/manual/en/function.call-user-func.php)
function. In other words, a callable can fulfil the responsibility of what we know as
functions. Functions and methods are always callables. Objects and classes may also become
callables.

A callable can, by definition, have its reference stored in a variable. Like the following:

```php
<?php

$callable = 'strlen';
```

What? But that's a string, no?

Well, yes. But it can be coerced into a callable if necessary. Like below:

```php
<?php

function callACallable(
  callable $f
): int {
  return $f('thephp.website');
}

$callable = 'strlen';

var_dump(
  $callable('thephp.website')
);
// int(14)

var_dump(
  callACallable($callable)
);
// int(14)
```

Callables may also point to object methods:

```php
<?php

class MyClass
{
  public function myMethod(): int
  {
    return 1;
  }
}

$obj = new MyClass();
var_dump([$obj, 'myMethod']());
// int(1)
```

Looks odd? I know it looks like an array. In fact it is. Unless you name it a callable 👀

This kind of callable above (object-method reference) is very interesting because
**you can call private or protected methods** with it **if you're inside the class' scope.**
Otherwise you may only call public methods with it.

Also classes that implement
[the __invoke() magic method](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke),
automatically transform their instances into callables themselves. Like the following:

```php
<?php

class MyCallableClass
{
  public function __invoke(): int
  {
    return 1;
  }
}

$obj = new MyCallableClass();
var_dump($obj());
// int(1)
```

**Main takeaway: callables hold reference to functions or methods and can be
constructed in different ways.**

<h4 id="compound-type-iterable">The iterable compound type</h4>

Iterables are simpler to explain: they are by definition an array or an instance of
the [Traversable interface](https://www.php.net/manual/en/class.traversable.php). The
main thing of an iterable is that it can be used in a
[foreach() loop](https://www.php.net/manual/en/control-structures.foreach.php), with a
[yield from statement](https://www.php.net/manual/en/language.generators.syntax.php#control-structures.yield.from)
or with [spread operator](https://wiki.php.net/rfc/spread_operator_for_array).

Examples of iterables are:

```php
<?php

function generator_function(): Generator
{
  // ...
};

// All variables here are iterables
$a = [0, 1, 2];
$b = generator_function();
$c = new ArrayObject();
```

**Main takeaway: if you can fit it in a foreach(), it is an iterable.**

### Special Types

There are two special types. And the biggest reason why they're called special, is that
**you can't cast these types**. The special types are both the **resource** type and
the **NULL** type.

**A resource represents a handle to an external resource**. It can be a file handle,
an I/O stream or a database connection handle. You may guess now why you can't convert
a resource to any other type.

**The null type represents a null value**. Meaning that a variable holding NULL was not
initialized, assigned to NULL or unset during runtime.

**Main takeaway: a special typed value can't be casted to anything.**

### What about class instances?

Class instances have the type `object` and will always be presented like so. Executing
[gettype()](https://www.php.net/manual/en/function.gettype) on an object will always return
a `string("object")` and calling [var_dump()](https://www.php.net/manual/en/function.var-dump)
on an object will always print its value using the `object(ClassName)` notation. If you need
to fetch an object's class as a string, use the
[get_class()](https://www.php.net/manual/en/function.get-class) function.

```php
<?php

$obj = new stdClass();

echo gettype($obj);
// object

var_dump($obj);
// object(stdClass)#1 (0) {
// ...

echo get_class($obj);
// \stdClass
```

<h2 id="type-operations-in-php">Type "operations" in php</h2>

There are different "operations" one can do with PHP when it comes to types. I believe
it is important to clearly state them here so that we don't mix things up later on.

### Type juggling: type casting and coercion

Before we dive in, here are three important definitions we need keep in mind:

1. **Type conversion** means to transform a type from A to B. For example: from integer to float.
1. **Type cast** means to **manually** or **explicitly** convert a type from A to B. As in `$hundred = (int) 100.0`. (`float(100.0)` became `int(100)`)
1. **Type coercion** means to **implicitly** convert a type from A to B. As in `$twenty = 10 + '10 bananas';`. (`string("10 bananas")` became `int(10)`)

Being that said, the following sections explain how it happens in php. Later on you'll find
more information on Type Juggling.

#### Type casting

Similar to what Java does, php allows type casting. Meaning that when a variable points to
a value that can be casted to a different type, it allows manual (explicit) type conversion.

Wait, wait. What!? 🤨

Given a variable `$hundred` holding a `string("100")` its value may be manually converted (casted)
to become an `int(100)` or a `float(100.00)` - or any other scalar type or one of compound
types _array_ or _object_.

The following snippet works just fine in PHP and is very similar to Java:

```php
<?php

$hundred = (int) '100';
// $hundred is now int(100)
```

Now, one thing Java does and is completely illegal on php code, is to convert (cast) a variable
pointer into a different class. **Meaning that we can only cast into scalar and some compound
types in php**:

```php
<?php

class MyClass {}

// Yields a parse error
$illegal = (MyClass) new stdClass();
```

Important to notice! Type casting in php is allowed into scalar types only*. Meaning that
casting an object into a different class instance is illegal, but **casting an object into
a scalar type is completely valid**.

**It is also possible to cast values into _array_ or _object_ types**, which aren't considered
scalar types but compound types (naming is really tough, huh?).

```php
<?php

class MyClass {}

$obj = new MyClass();
$one = (int) $obj; // int(1)
```

The code above generates some notices but is still valid. Later on I'll explain where this
`int(1)` value came from.

**Main takeaway: php allows casts into scalar types, arrays or objects. Class casting is not
allowed.**

#### Type coercion

**Type coercion happens as a side-effect of working with mismatched or undeclared types.**
It is explained in depth later on in this article. For now just know that php will
automatically cast types in your code during runtime when necessary.

An example of type coercion can be multiplying an integer by a float number. The expression
`int(100)` multiplied by a `float(2.0)` results in a `float(200)` value.

```php
<?php

var_dump(100 * 2.0);
// float(200)
```

**Main takeaway: php has a mechanism to normalize types in runtime implicitly and you should
always watch out for it.**

### Type hinting

Type hinting is both a coercion enforcement and a strict typing mechanism. It was introduced
to php language in version 7.0 and affects function and method signatures.
[Since php 7.4 it is also possible to type-hint class properties](https://wiki.php.net/rfc/typed_properties_v2).

Here's an example of type hint:

```php
<?php

function sum(
  int $a,
  int $b
): int {
  return $a + $b;
}
```

The hints here say that variable `$a` is naturally or coerced of type _int_, the variable `$b`
is naturally or coerced of type _int_ and the result of this function is naturally or
coerced of type _int_.

Did you notice how I used "naturally or coerced" above? That's because PHP won't complain if
you call this function with non-integer values. In fact, it will attempt to implicitly
convert (coerce) your parameters into integers if they aren't already.

**In this function's body you can always trust that `$a` and `$b` are integers. But that they
will have the expected integers, will depend on the function's caller.**

```php
<?php

function sum(
  int $a,
  int $b
): int {
  // $a is int(10)
  // $b is int(10)
  return $a + $b;
}

sum('10 apples', '10 bananas');
```

It is also possible to use a php directive named `strict_types` to avoid coercions and simply
raise errors when a type mismatch occurs. Like the following:

```php
<?php

declare(strict_types=1);

function sum(
  int $a,
  int $b
): int {
  return $a + $b;
}

sum('10 bananas', '10 apples');
// PHP Fatal error: Uncaught
// TypeError: Argument 1 passed
// to sum() must be of the type
// int, string given
```

**It doesn't mean that php is statically typed when strict types are switched on!**
In fact, type hinting only adds processing overhead to the php engine. Internally it
will always perform type juggling and won't ever trust your variable type hints.

Type hints only serve two purposes: define into which types a value should be coerced
into OR to raise fatal errors when strict types are switched on.

**Main takeaway: type hinting gives mere hints about types to the engine, not orders.
Being strict about typing is your choice and will bring a small overhead with it.**

<h2 id="union-types">Union Types</h2>

Before we move to type juggling I'd like to quickly talk about Union Types because it
seems to make more sense here.

Besides all three types php has (scalar, compound and special) the php manual also
mentions a [pseudo-type which exists for readability purposes only](https://www.php.net/manual/en/language.pseudo-types.php).
This type doesn't really exist, is just a convention.

I want you to pay attention to one very specific pseudo-type: the `array|object`
pseudo-type is often used in the documentation to specify parameters or return types.

The `iterable` type is also a kind-of Union Type. It can be defined as `array|Traversable`.

Since php 7.1 the language added a kind-of support to union types by introducing the
[nullable types](https://wiki.php.net/rfc/nullable_types). If you really think about
a nullable type is just an union of `T|null`. For example `?int` means `int|null`.

I bet you never thought about that before! 😝

So after so many unknown union types, [php 8.0 comes with a proper Union Types feature](https://wiki.php.net/rfc/union_types_v2).
Where you can define any union you need without having to depend on pseudo-types or
conventions. It works like this:

```php
<?php

declare(strict_types=1);

function divide(
  int $a,
  int $b
): int|float {
  return $a / $b;
}
```

The above function may return an integer or a float. But never anything else.

<h2 id="php-type-juggling">PHP's type juggling</h2>

Probably it isn't the first time you've heard the term Type Juggling, is it? This
is one of the most important core features php offers and yet it is one of the
least understood ones.

I can't blame people for not knowing it. It is called "juggling" for a reason.
There are so many variations of what a variable type can be in each context
that it can become quite cumbersome to understand with which type you're dealing.

Let's start with the following statement: **php does not support explicit type
definition in variable declaration**. And this is very powerful!

Whenever you declare a variable php will infer which type it should contain based
on its assignment. While `$var;` creates a variable with a NULL value, `$one = 1;`
creates an integer and `$obj = new stdClass()` creates an `object(stdClass)`.

There's no type definition at all! PHP tries its best to guess what type should
be attributed to a variable.

PHP variables are very dynamic, to the point they may change type in runtime without
any trouble! The code below is valid:

```php
<?php

$var;
// $var is NULL

$var = 1;
// $var is int(1)

$var = 'thephp.website';
// $var is string("thephp.website")

$var = new stdClass();
// $var is object(stdClass)
```

And because variables are so dynamic many operations in php require the engine to
check their values based on the operation's context. An expression like sum (a + b)
will internally check for the first operand's type and later on guess the second
operand's type.

Take a look [at this snippet from php's source code](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L47-L84).
If `op1` is long (a is integer) then check if `op2` is long (b is integer). If so,
perform a long sum otherwise check if `op2` is double and perform a double sum if yes.
And this expression [may return an integer](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L61)
or [a float](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L74).

**That's why you can simply take for granted that type juggling will happen automatically.**

This also means that type coercion (implicit casts) will also happen automatically. But
they aren't supposed to be a surprise! There are very specific moments where a type coercion may occur.

Type coercions (and therefore juggling) may occur:
* when resolving an expression
* when passing arguments to a function or method
* when returning from a function or method

You might be thinking: well, if coercions are everywhere then how does php handle
incompatible types? Converting an integer to a boolean seems ok, but an array to
integer sounds a bit awkward already.

Well, php has very well defined rules for type casting. The first thing necessary
is to understand what is the resulting type supposed to be and later on evaluate
the casting.

For example, if an expression occurs inside an `if()` statement we can quickly
realize that the expression should yield a boolean value.

```php
<?php

$var = 100;
// $var is int(100)

// $var is treated as bool
// and evaluates to TRUE
if ($var) {
  // $var is still int(100)
}

// $var is still int(100)
```

Notice how $var was `int(100)` during its entire lifetime, but got treated as `bool(TRUE)`
inside that _if()_ statement. That's because the _if()_ statement expects an expression
that evaluates to boolean as result. The type juggling is exactly what php does under the
hood for you.

To illustrate, here goes the decision tree for converting a type to boolean.
**Boolean conversion will return false when the original value is**:

* a bool(FALSE)
* an `int(0)` or `int(-0)`
* a `float(0)` or `float(-0)`
* an empty `string("")` or the zero `string("0")`
* an empty `array()`
* a NULL
* a SimpleXML instance created from empty tags

**And it will return true for everything else.**

The above table can be found in the
["Converting to boolean" section of the manual](https://www.php.net/manual/en/language.types.boolean.php#language.types.boolean.casting).

A complete documentation on type comparisons and conversion tables
[can also be found in the language manual](https://www.php.net/manual/en/types.comparisons.php).
I don't have the courage to read it myself, but is part of my job to make it available here 🤷🏻‍♀️

**Important note here**: in php 8.0 union types were introduced to the language bringing an extra
layer of complexity. Type juggling, when dealing with union types, must follow a precedence. And
this precedence is well-defined, instead of based on type order.

[So if you're not using strict_types your union types will follow this rule](https://wiki.php.net/rfc/union_types_v2#coercive_typing_mode).
If the union type doesn't contain the subject's type, it may coerce its value in the following
order of precedence: `int`, `float`, `string` and `bool`.

For example:

```php
<?php

function f(
  int|string $v
): void {
  var_dump($v);
}

f(""); // string IS in the union type
// string("")

f(0); // int IS in the union type

f(0.0); // float ISN'T in the union type
// int(0)

f([]); // array ISN'T in the union type
// Uncaught TypeError:
// f(): Argument #1 ($v)
// must be of type string|int
```

In the above example something very interesting happens! The array type won't be converted
to a `bool(FALSE)`. It raises a TypeError instead!

<h2 id="php-type-modes">PHP type modes</h2>

**You have already realized that there are two ways php can handle types**. One way is
called **"Coercive Type Mode"** with all these juggling and guessing games happening. The
other way is the **"Strict Type Mode"** where **juggling and guessing still happens**, but
**when explicitly defined types are set**, some **TypeErrors will be thrown if type
mismatches happen**.

Now, I see it as a very common thing that php developers expect the engine to respect the
Law of Equivalent Exchange (等価交換法), and pay our effort of strictly typing everything
with a performance boost, because then it will be finally able to bypass all type checks
and perform operations right away.

While I see the reason why someone might think this way, I must tell you: it is completely
wrong! Let's check the following the
[strlen() logic in php's source code](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L8056-L8105).

Every time we need to check whether we're using strict mode, we get the boolean value
from `EX_USES_STRICT_TYPES()`. If true, we're in strict mode. If false, we're in coercive mode.

Now, check the snippet again! It starts like this:

```c
// ...
zval *value;

value = GET_OP1_ZVAL_PTR_UNDEF(BP_VAR_R);
// value is the parameter of
// strlen()

if (EXPECTED(
  Z_TYPE_P(value) == IS_STRING
)) {
  ZVAL_LONG(
    EX_VAR(
      opline->result.var
    ),
    Z_STRLEN_P(value)
  );
  FREE_OP1();
  ZEND_VM_NEXT_OPCODE();
} else {
  // ...
}
```

Do you see that first _if()_ statement there? Guess what it is doing... EXACTLY! It checks
for your parameter's type!!

Do you know what this snippet is doing with your type hints? NOTHING! 🤣

The _else_ clause holds the interesting stuff that MIGHT use the strict types or not.

```c
// ...
} else {
  // Looks promising
  zend_bool strict;

  // 😭
  if (
    (OP1_TYPE & (IS_VAR|IS_CV)) &&
    Z_TYPE_P(value) == IS_REFERENCE
  ) {
      // ...
  }

  // ...

  // 👀
  strict = EX_USES_STRICT_TYPES();
  do {
    if (EXPECTED(!strict)) {
      // ...
    }
    zend_internal_type_error(
      strict,
      /*...*/
    );
    ZVAL_NULL(
      EX_VAR(opline->result.var)
    );
  } while (0);
}
```

From the snippet above we can see an example of how the strict mode doesn't cut any
processing. In fact, it creates a couple of extra checks with a single purpose: to raise
fatal errors.

I'm not saying it is a bad implementation. I'm personally very happy with it. But I think
it is important to make it clear that it won't affect performance positively.

**Main takeaway: strict types won't make your code faster!**

## Closing Thoughts

This article was a big quest. I'm seriously thinking about writing a book. This page
alone would make up for 15% of a good-sized book already 😂

I hope the information I've collected here is useful for you. And if not, at least interesting.

I believe php's type system is incredibly rich and carries many innovative and legacy
features and they all make much sense when you look at the history of the language development.

As usual, feel free to ping me on twitter if you have anything to say. Open an issue or pull
request and be happy.

**Main takeaway: it took me an incredible amount of time to write this article. If you'd like
to show any kind of support, please share it in your social media and social circles 🙏**

See you next time! Cheers!

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
  "headline": "Everything you need to know about PHP's type system",
  "description": "This is the absolute best guide you'll find in the internet about how php handles typing internally and in userland.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/17-php-type-system-640.webp"
   ],
  "datePublished": "2020-07-25T00:00:00+08:00",
  "dateModified": "2020-07-25T00:00:00+08:00",
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
