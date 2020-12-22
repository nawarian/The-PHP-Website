---
slug: state-of-php-8
canonical: https://thephp.website/en/issue/php-8-features/
title: The State of PHP 8
category: walkthrough
createdAt: 2020-01-20
sitemap:
  lastModified: 2020-01-20
image:
  url: /assets/images/posts/5-php-8-640.webp
  alt: 'A number eight written with engine chains'
tags:
  - php8
  - core
  - news
meta:
  description:
    PHP 8.0 is still under discussion and many things are being
    voted right now. I've collected all changes introduced
    to PHP 8.0 and will keep you posted under this one post.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/andamento-php-8/)

**Important update!** PHP 8.0 was already released. You can check out [this great
summary containing the main news on this version](/en/issue/php-8-features/).

PHP 8.0 is [currently being discussed and developed](/en/issue/php8-release-schedule/).
This means that many things in this post will still change a lot over time. For each field
of interest I'll leave a subheading and as discussions move further inside php community
they'll be properly updated.

It should be clear that I won't be able to post every single update in real time as there
are many things happening every day. If you're looking for a fresh up-to-date list, please
refer to the [UPGRADING file in the official repo](https://github.com/php/php-src/blob/master/UPGRADING).

## When will PHP 8.0 be released?

**PHP 8.0 will be released on December 2020.** At least this is where the official schedule is
pointing to.

[PHP 7.4 got released not long ago](https://thephp.website/en/issue/php-75-will-be-skipped/) and there
won't be another minor version (PHP 7.5) for this major 7. So the next version will
definitely be PHP 8.

<hr>

## Accepted features for PHP 8.0

Features listed below **will be delivered within PHP 8.0's release**. They were
already voted, accepted AND implemented.

So if you want to have a taste of this version, check this section out:

<!-- https://wiki.php.net/rfc/jit -->
### JIT: Just in Time Compiler
* **Status**: Confirmed.
* **Category**: Performance.
* **Votes**: 50 yes. 2 no.

I wrote an [in-depth explanation about what the heck the Just In Time compiler is](/en/issue/php-8-jit/).
 
This feature claims to be **more than four times faster** on Mandelbrot benchmark
and should cause big impacts on CPU-bound operations.

You can check the full spec by [visiting the RFC page](https://wiki.php.net/rfc/jit).

<!-- https://wiki.php.net/rfc/union_types_v2 -->
### Union Types V2
* **Status**: Confirmed.
* **Category**: Syntax.
* **Votes**: 61 yes. 5 no.

The [Union Types V2 RFC](https://wiki.php.net/rfc/union_types_v2) will allow
every type definition to explicitly tell what possibilities are accepted, instead
of trusting the good old _mixed_.

New syntax will look like the following:

```php
function myFunction(int|float $number): int
{
  return round($number);
}
```

<!-- https://wiki.php.net/rfc/weak_maps -->
### The WeakMap class
* **Status**: Confirmed.
* **Category**: Standard Library.
* **Votes**: 25 yes. 0 no.

The [WeakMap class RFC](https://wiki.php.net/rfc/weak_maps) creates a new class
called `WeakMap` which looks a bit like `SplObjectStorage`.

The main idea is that you can create a `object -> value` map in it, without
preventing this object from being garbage collected. Thus the `Weak` name, stating
there's a **weak reference between the key object and the map itself**.

Garbage collecting an object used as key in such map will cause its removal, meaning
the value will be removed. Like the following:

```php
$map = new WeakMap();
$obj = new DateTime('today');

$map[$obj] = 100;

// Shows one key
var_dump($map);

// Remove $obj from memory
unset($obj);

// WeakMap is now empty
var_dump($map);
```

**Edit (2020.01.20)**: if you want to give it a try, there's already a polyfill
implementation that works with PHP 7.4; It is called [BenMorel/weakmap-polyfill](https://github.com/BenMorel/weakmap-polyfill).

<!-- https://wiki.php.net/rfc/consistent_type_errors -->
### TypeError exceptions will be thrown on parameter parsing failures
* **Status**: Confirmed.
* **Category**: Standard Library.
* **Votes**: 50 yes. 2 no.

Whenever you cause a type error on user-defined functions, it will throw
an exception. For internal functions, though, PHP shows a warning and
returns null by default.

[The consistent TypeError RFC](https://wiki.php.net/rfc/consistent_type_errors)
makes both behaviours consistent, by throwing TypeError exceptions in
both cases.

<!-- https://wiki.php.net/rfc/negative_array_index -->
### Implicit array keys will be more consistent
* **Status**: Confirmed.
* **Category**: Standard Library.
* **Votes**: 17 yes. 2 no.

Whenever you use negative indexes on the `array_fill` function, it will
generate the first negative index and then jump to 0 (🤦‍♀️). Like this:

```php
$a = array_fill(-2, 3, true);
var_dump($a);

// outputs
array(3) {
  [-2] =>
  bool(true)
  [0] =>
  bool(true)
  [1] =>
  bool(true)
}
```

So the [Negative Array Index RFC](https://wiki.php.net/rfc/negative_array_index)
aims to fix this behaviour by letting `array_fill` properly step with negative
indexes:

```php
$a = array_fill(-2, 3, true);
var_dump($a);

// outputs
array(3) {
  [-2] =>
  bool(true)
  [-1] =>
  bool(true)
  [0] =>
    bool(true)
  }
```

<!-- https://wiki.php.net/rfc/lsp_errors -->
### Fatal Error on wrongly typed inherited methods
* **Status**: Confirmed.
* **Category**: Standard Library.
* **Votes**: 39 yes. 3 no.

Whenever a class defines a method signature and its children attempt to
overload such method (by changing its signature) a warning is thrown.

[This RFC from Nikita Popov](https://wiki.php.net/rfc/lsp_errors) makes
this behaviour to throw a Fatal Error whenever this overload is attempted.

Here's an example of buggy code on PHP 8:

```php
class A
{
  function x(int $a): int
  {
    // ...
  }
}

class B extends A
{
  // Notice the signature
  // changed. Fatal Error here.
  function x(float $a): float
  {
    // ...
  }
}
```

<!-- https://wiki.php.net/rfc/dom_living_standard_api -->
### DOM API upgrade to match latest standard version
* **Status**: Confirmed.
* **Category**: Standard Library.
* **Votes**: 37 yes. 0 no.

[This RFC](https://wiki.php.net/rfc/dom_living_standard_api) also requires a
post by itself.

But basically it adds a couple of interfaces and classes to make `ext/dom` API
to match the [current DOM standard](https://dom.spec.whatwg.org/) which is
constantly changing.

<hr>

## What MIGHT enter PHP 8.0 version?

There are a couple of RFCs still under discussion. They might be denied or
accepted any time soon. There are many things related to the core of the
language and its syntax.

Here goes the list:

<!-- https://wiki.php.net/rfc/engine_warnings -->
### Severity levels for errors messages fixed
* **Status**: Accepted. Pending Implementation.
* **Category**: Standard Library.

The [severity error messages' levels RFC](https://wiki.php.net/rfc/engine_warnings)
aims to make a revision on many core error handling features.

For example the widely known `Invalid argument supplied for foreach()`
might jump from `Warning` to `TypeError Exception`.

<!-- https://wiki.php.net/rfc/class_name_literal_on_object -->
### Allow ::class access on objects
* **Status**: Implemented. Under Discussion.
* **Category**: Syntax.

Basically Dynamic class names aren't allowed in compile time. So a code
like the following raises a fatal error:

```php
$a = new DateTime();
var_dump($a::class);
// PHP Fatal error:  Dynamic
// class names are not allowed
// in compile-time
// ::class fetch in...
```

With [this RFC](https://wiki.php.net/rfc/engine_warnings) it will be now possible.

<!-- https://wiki.php.net/rfc/static_return_type -->
### Make _static_ a valid return type, like _self_
* **Status**: Implemented. Under Discussion.
* **Category**: Syntax.

Just the way we can make `self` a return type for functions,
[the static return RFC](https://wiki.php.net/rfc/static_return_type) aims to make
`static` also an available return type.

This way **functions like the following would be then correct:**

```php
class A
{
  public function b(): static
  {
    return new static();
  }
}
```

<!-- https://wiki.php.net/rfc/variable_syntax_tweaks -->
### Consistent variables syntax
* **Status**: Implemented. Under Discussion.
* **Category**: Syntax.

This one is also about syntax and will change a couple of features.

I recommend you checking out [the RFC](https://wiki.php.net/rfc/variable_syntax_tweaks)
for more details. Affected language features include:

* Interpolated and non-interpolated strings
* Constants and magic constants
* Constant dereferencability
* Class constant dereferencability
* Arbitrary expression support for new and instanceof

<!-- https://wiki.php.net/rfc/use_global_elements -->
### Optimize function/constants lookup
* **Status**: POC implemented. Under Discussion.
* **Category**: Syntax. Performance.

The [RFC about function and constants lookup](https://wiki.php.net/rfc/use_global_elements)
adds a new `declare()` statement that prevents PHP from performing lookups
on runtime.

Whenever you're in a namespaced code and tries to fetch a globally scoped
function or constant without prefixing it with a backslash (`\`), PHP
will first try to find it on the current namespace and then bubble up
to the global namespace.

By adding a `disable_ambiguous_element_lookup=1` directive, PHP will directly
go to the global namespace. Here's an example (from the RFC):

```php
namespace MyNS;
declare(
    strict_types=1,
    disable_ambiguous_element_lookup=1
);
use function OtherNS\my_function;
use const OtherNS\OTHER_CONST;
 
if (
  // function lookup!!
  version_compare(
    // constant lookup!!
    PHP_VERSION,
    '8.0.5'
  ) >= 0
) {
    // ...
}
```

`disable_ambiguous_element_lookup` was `zero` on the above example,
PHP would attempt to find `MyNS\PHP_VERSION` and `MyNS\version_compare`
first, understand they don't exist (hopefully) and only then attempt the
`\PHP_VERSION` and `\version_compare`.

When `disable_ambiguous_element_lookup` equals `one`, this extra lookup
is no longer necessary and PHP will go directly to the global scope, fetching
`\PHP_VERSION` and `\version_compare`.

<!-- https://wiki.php.net/rfc/strict_operators -->
### Strict Operators directive
* **Status**: POC implemented. Under Discussion.
* **Category**: Syntax.

[The strict operators RFC](https://wiki.php.net/rfc/strict_operators) would
add a new directive called `strict_operators`. When switched on a couple of
comparisons would then behave differently.

Here are some examples on how php would behave (from the RFC):

```php
10 > 42;        // false
3.14 < 42;      // true
 
"foo" > "bar";  // TypeError("Unsupported type string for comparison")
"foo" > 10;     // TypeError("Operator type mismatch string and int for comparison")
 
"foo" == "bar"; // false
"foo" == 10;    // TypeError("Operator type mismatch string and int for comparison")
"foo" == null;  // TypeError("Operator type mismatch string and null for comparison")
 
true > false;   // true
true != 0;      // TypeError("Operator type mismatch bool and int for comparison")
 
[10] > [];      // TypeError("Unsupported type array for comparison")
[10] == [];     // false

"120" > "99.9";               // TypeError("Unsupported type string for comparison")
(float)"120" > (float)"99.9"; // true
 
"100" == "1e1";               // false
(int)"100" == (int)"1e2";     // true
 
"120" <=> "99.9";             // TypeError("Unsupported type string for comparison")
```

Changes are much wider than this example and are out of the scope of this single
post. Check the RFC for more, or ping me on twitter if you'd like to see a blog
post about this one! 😉

<hr>

The RFCs below are still under discussion and most of them have something related
to past versions of PHP, not being able to get released in time or something
similar. I won't describe them in detail just yet, as I don't quite feel they
have big changes to be integrated to the language.

I will, of course, follow up on them to make sure I'm hopefully wrong.

Here they are:

<!-- https://wiki.php.net/rfc/normalize-array-auto-increment-on-copy-on-write -->
### Auto Increment value on copy on write
* **Status**: Under Discussion.
* **Category**: Syntax.

[RFC link.](https://wiki.php.net/rfc/normalize-array-auto-increment-on-copy-on-write)

This RFC was originally targeting PHP 7.4 but is still marked as Under Discussion.
So I'd expect it to retarget PHP 8.0 this time.

<!-- https://wiki.php.net/rfc/alternative-closure-use-syntax -->
### Alternative "use" syntax on Closures
* **Status**: Under Discussion.
* **Category**: Syntax.

[RFC link.](https://wiki.php.net/rfc/alternative-closure-use-syntax)

This RFC was originally targeting "the next minor version", which at that time would
be php version 7.4.

<!-- https://wiki.php.net/rfc/namespace_scoped_declares -->
### Apply a declare() to an entire Namespace 🔥
* **Status**: Implemented. Under Discussion.
* **Category**: Syntax.

[RFC link.](https://wiki.php.net/rfc/namespace_scoped_declares)

<!-- https://wiki.php.net/rfc/trailing_whitespace_numerics -->
### Permit trailing spaces on numeric strings
* **Status**: Implemented. Under Discussion.
* **Category**: Syntax.

[RFC link.](https://wiki.php.net/rfc/trailing_whitespace_numerics)

This RFC also targeted version 7.4 and didn't make it in time.

<!-- https://wiki.php.net/rfc/nullable-casting -->
### Allow nullable type casting
* **Status**: Lost. Under Discussion.
* **Category**: Syntax.

[RFC link.](https://wiki.php.net/rfc/nullable-casting)

Apparently the fork containing the Work In Progress for such change got deleted. And the
Pull Request closed. Doesn't seem like it will ever be integrated unless someone
take over this one.

<hr>

So far, that's it. I'll add some **Edit** on topics over time, as the community
moves forward and I get the opportunity to see statuses changing.

If you found something wrong or would like to add something missing here,
feel free to ping me on twitter or open an issue on the
[public repository](https://github.com/nawarian/The-PHP-Website).

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
  "headline": "The State of PHP 8",
  "description": "PHP 8.0 is still under discussion and many things are being voted right now. I've collected all changes introduced to PHP 8.0 and will keep you posted under this one post.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/5-php-8.jpg"
   ],
  "datePublished": "2020-01-20T00:00:00+08:00",
  "dateModified": "2020-01-20T00:00:00+08:00",
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
