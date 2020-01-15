---
slug: you-thought-you-knew-php-generators
title: So you thought you knew about php generators
createdAt: 2019-12-11
sitemap:
  lastModified: 2019-12-23
image:
  url: /assets/images/3-generators.jpg
  alt: 'A computer screen being magnified by a pair of glasses'
meta:
  description:
    In this post I talk about PHP Generators, how to work with Coroutines and
    how the language ecosystem could evolve with it.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/voce-achou-que-sabia-sobre-generators/)

## TL;DR

Generators are much more than yielding values to avoid
using arrays. They provide us with the power of async,
coroutines and dark magic 🧙‍!

If you seek a more complete and mind blowing explanation,
please read [this article from 2012 by nikic](https://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html).

# What are Generators and what do they do?

Let's start from [the official documentation](https://www.php.net/manual/en/language.generators.overview.php). We'll find many clues
from there!

Generators are a php feature since version 5.5, as you
can see from the [generators RFC](https://wiki.php.net/rfc/generators).

The main idea of Generators is to provide a simple **way
to write iterators** without having to implement the
Iterator interface. Generators also provide us with a
way of **interrupting code execution**, which is
extremelly cool!

The way it works is by using the `yield` keyword inside
functions. By doing this the **return type** of your function
**automatically turns into [`\Generator`](https://www.php.net/manual/en/class.generator.php)**.

**So be aware!** The code below _breaks_ because forces
a return type but the generator changes it to another:

```php
// String as return type, fails
function myGeneratorFunction(): string
{
  yield 'Generators'; // Transform the return type into \Generator

  // Fatal Error: return type is \Generator, we're returning string
  return 'The PHP Website';
}
```

By allowing us to interrupt code execution, it naturally
provides a way to better manage memory usage in our php
programs. There's a very famous script that illustrates this:

```php
// Regular range
foreach (range(1, 10000) as $n) {
  echo $n . PHP_EOL;
}

// Generator-based range
function xrange(int $from, int $to) {
  for ($i = $from; $i <= $to; $i++) {
    yield $i;
  }
}

foreach (xrange(1, 10000) as $n) {
  echo $n . PHP_EOL;
}
```

The difference? Over simplification alert: `range()`
allocated memory for 10,000 integers, while `xrange()`
allocated memory for only one.

You probably knew this since 2012, yes. Let's just quickly
summarize this part and jump to the fun!

## What PHP Generators do?

Generators provide us with a **simple way of creating iterators**
with no need to implement the Iterator interface and allow
**code interruption** for better memory management or any sort
of crazy stuff you might want to come up with.

Below I'll show a generator sample and comment out some terms.
If while readig this text you find a keyword you find weird,
come back to this sample ;)

```php
// Generator function
function xrange(): \Generator {
  // Generator's context/body/scope
  while (true) {
    yield 1;
  }
}

$xrange = xrange();
$xrange->next(); // Pulls next yield
```

# What you possibly don't know about php Generators, though...

The amazing feature from generators that people often
miss is the capacity of **pushing values back to the generator
function**.

Basically when you `yield` inside a generator function,
code stops executing there and goes back to the upper
context (caller's context). From there, though, the caller
context can actually push values inside the generator
function's context.

This creates a great set of opportunities for building
amazing tools that negotiate processing flow for you.
Including **coroutines**, **asynchronous programming** and
**optimizing data fetching**. You'll love this last one,
bare with me!

## How to push data back to the generator function?

Actually is quite simple. A Generator object contains
all Iterator methods and a few more. One of them is the
[`Generator::send()`](https://www.php.net/manual/en/generator.send.php)
method, which is used to push data back to the generator
function's context.

The way it works is the following:
1. The caller triggers the generator function execution
1. The generator function yields something, interrupting the
code execution and coming back to the caller
1. The caller calls the generator's `send()` method, which pushes
a value as a result of the previous yield statement
1. Generator function keeps executing with this value now available.

Less words, more code:

```php
function myGenerator(): Generator
{
  // 2. yield something back to the caller
  $twenty = yield 10;

  // 4. Keep execution with new value
  var_dump($twenty);
}

$gen = myGenerator();
// 1. Trigger execution
$ten = $gen->current();

// 3. Push back a value to generator
$gen->send($ten * 2);

// Output: int(20)
```

**That's pretty much everything you'd want to know about Generators.**
I mean, you can also throw exceptions to the generator function's
context by using `Generator::throw()` method. But that's actually
everything...

**But of course we have more to see!** You didn't expect me to
come here with content you could easily find anywhere else,
did you?

By following nikic's post (the 2012 post mentioned above) you
can extract much deeper and detailed information on what you
can do with php Generators. Go ahead and read it as many times
you feel like is necessary to absorb the idea.

That's all theory and it is really cool. But there are some
amazing **concrete applications of Generators that can change
your life** or at least bring you to consider a different
paradigm.

# What are PHP Generators used for?

I'd like to present you **two great applications for php Generators**.
One is open source and can be used right away, the other one is
more of a concept and you'd have to develop something by your own.

## Async development: how Amp framework works

I know, you probably already heard about Amp framework
and how it can help you developing async code with PHP.

**But I'm here to take you out from the user land.** I want you
to ponder about how it was implemented and have at least a
broad view on how it works and heavily depend on Generators.

Consider the following incomplete example:

```php
Amp\Loop::run(function () {
  $socket = yield connect(
    'localhost:443'
  );

  // object(EncryptableSocket)
  var_dump($socket);
});
```

Pooah, so many things happening here...

Begin with the idea that `Amp\Loop::run()` creates an Event Loop.
If you don't know what an event loop does, stop here and go read
a bit about it. You'll find things about React PHP and Node.JS.

In fact, please learn a bit about React PHP and how it enqueues
tasks to run and how it polls for changes on I/O operations,
allowing you to perform async programming with PHP.

The thing is that this Event Loop from Amp is very special
because **it is not only an event loop**. It also watches for
yielded values and **expects your callback function to be a
Generator function**!

So besides doing the whole tasks queue and monitoring I/O
operations to keep you unblocking the main thread, it will also
handle values you yield.

By equiping itself with [React Promises](https://github.com/reactphp/promise)
**it emulates an await/async feature on PHP**.

But how?

If you look close to the [implementation of the connect function](https://github.com/amphp/socket/blob/d49dc0d7936f65fd41068482da801768266d0c1a/src/functions.php#L63)
you'll notice that it returns a Promise that when resolved will
return an `EncryptableSocket` object.

So `connect('localhost:443');` actually returns a Promise instance.
How come `$socket` contains `EncryptableSocket` instead?

The moment we yield a Promise instance inside the Event Loop,
Amp will wait for this promise to resolve or reject. So it either
**pushes the resolved value back** OR **throws an exception inside
your generator function**.

**Really, how cool is that!**

Does it mean you should write your applications like this from now
on? Maybe, maybe not...

Even though it is really really cool that we don't need to hope for
async/await to be integrated to the core library, this approach
feels a bit invasive for type freaks.

First of all, it forces you to always have Generator as return type
for your main loop. Which is fine. But then, in order to take real
advantage on this **we also need to return Promises everywhere**.

Which is also fine, JavaScript people do this all the time and don't
freak out. But without Type Generics we can't really enforce that
a certain Promise will resolve a certain type.

If you're fine with it, go ahead. There's a whole new world to be
explored! Just check out [the currently available packages on Amp Framework](https://amphp.org/packages)
so you don't reinvent the wheel.

## Optimized data retrieval with Generators

We're in this kind of web api era, which is really cool. There
are many patterns to follow as API provider in PHP: SOAP, REST,
GraphQL... This leaves little space for traditional MVC
applications as we used to see a couple of years ago.

Things like REST tend to decrease our data dependency tree a
lot: you specialize in one resource per URL, which eventually
you can precompute and place in a very fast data storage.

But whenever you think of multiple data sources to compose a
response Generators can be an incredible tool to optimize
our timings and resource usage.

For REST APIs maybe not, but thinking of GraphQL it is natural
that resource fetching management is important. Performance talks
and we're empowered to make it right.

In [this amazing presentation by Bastian Hoffmann](https://www.youtube.com/watch?v=YYt9u4uUetU)
we can get some inspiration from the moment he starts talking
about widgets. The whole idea of having a core request handler
for a resource type that is composed of other, smaller, resources
and having this dependency tree organized can yield great benefits.

Imagine the following GraphQL request (syntax simplified):

```gql
{
  person {
    name
  }
  team {
    people {
      name
      age
    }
  }
}
```

The array of `people` might contain the same `person` object
among its elements so why request `person` twice? Just because
once only "name" was requested and the later case "name" and
"age"? We can optimize this to a single call.

So why not having something similar to the following?

```php
// PersonType.php
yield DataRequirement::craft(
  Person::class,
  ['name'],
  $whereClause
);

// PeopleType.php
yield DataRequirement::craft(
  Person::class,
  ['name', 'age'],
  $whereClause
);
```

Looks a tad weird, right? It is indeed, given php engineers usually
have a very straight forward life cycle on their applications.

Just imagine how cool that would be if the handler calling `PersonType.php`
would be the same as the one calling `PeopleType.php` and
by yielding those requirements a `Resolver` would understand
they need the exact same entity and optimize the REST/SOAP/MongoDB
request to fetch only necessary fields once.

I recently started a POC on how to develop such thing. It looks
like the following snippet (which you can also [find here](https://github.com/nawarian/resolver/blob/master/tests/integration/FetchThePhpWebsiteSitemapsTest.php#L43-L68)).

```php
public function fetchSitemaps(): Generator
{
  // Wrap a service to always return a Promise.
  // Could be done via Annotations maybe
  $sitemapProvider = wrap($this->sitemapService);

  list($this->en, $this->br) = yield [
    $sitemapProvider->getSitemap('en'),
    $sitemapProvider->getSitemap('br'),
  ];

  // Here $this->en and $this->br are
  // populated with getSitemap() results
}
```

The more I kept developing this POC, the more it looked
like Amphp. So I guess [that'd be the way to go.](https://amphp.org/amp/coroutines/)

In general, I see great potential on Coroutines for PHP
and would love to see this side of the language developing
more.

Let me know what you think! Just ping me on Twitter
and develop this idea together 😉

<div class="align-right">
  --
  <a href="https://twitter.com/nawarian">
    @nawarian
  </a>
</div>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "TechArticle",
  "headline": "So you thought you knew about php generators",
  "description": "In this post I talk about PHP Generators, how to work with Coroutines and how the language ecosystem could evolve with it.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/3-generators.jpg"
   ],
  "datePublished": "2019-12-11T00:00:00+08:00",
  "dateModified": "2019-12-11T00:00:00+08:00",
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
