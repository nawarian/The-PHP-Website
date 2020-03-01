---
slug: how-does-php-engine-actually-work
title: How the php engine works internally
category: walkthrough
createdAt: 2019-11-09
sitemap:
  lastModified: 2020-02-29
image:
  url: /assets/images/posts/2-engine-640.webp
  alt: 'A picture focusing components of a server'
tags:
  - core
  - languages
meta:
  description:
    Let's do a quick overview on how php engine works
    both as a web engine and how the language
    behaves internally within the Zend VM.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/como-php-funciona-na-verdade/)

## TL;DR

PHP as an engine parses files into tokens, builds an Abstract
Syntax Tree (AST) and later on transform this tree into Opcodes.
Such Opcodes can be cached for performance.

On web servers PHP is normally used with PHP-FPM, which brings
amazing scaling capabilities to it. PHP 7.4 also brought a
preloading feature, that is capable of parsing php files into
Opcodes while FPM service is being lifted.

## So... what is PHP?

@php
  $today = date_create('today');
  $phpReleaseDate = date_create('1995-06-08');
  $yearsSinceFirstRelease = $today->diff($phpReleaseDate)->y;
@endphp

Makes me very happy to see how modern PHP development
requires less and less knowledge to build great products.
**This is really incredible**.

With little research we come to the information that PHP
first became public about [{{ $yearsSinceFirstRelease }} years ago](https://groups.google.com/forum/#!msg/comp.infosystems.www.authoring.cgi/PyJ25gZ6z7A/M9FkTUVDfcwJ).

The way it began is extremelly important to understand
why and how it got so popular. It also makes much easier
to understand most of its architectural traits and decisions.

PHP was born as a templating engine. It was basically a set
of CGI programs to enable web pages development with dynamic
values.

Yes, it was supposed to mix HTML and PHP code. But in 1995,
this was a bomb!

As a scripting language, php runs code sequentially from
top to bottom and every new execution is 100% fresh. No
memory or context sharing.

When it comes to web pages, though, this might change a bit.
Given PHP can run both as a CGI or module in your http server.
Let's give it a closer look.

### How does PHP work with HTTP Servers?

In general HTTP servers have a very clear responsibility:
provide hypermedia content using the [HTTP Protocol](https://tools.ietf.org/html/rfc2616#page-7).
This means that **http servers would receive a request,
fetch a string content from somewhere, and respond
with this string** based on the HTTP Protocol.

PHP came to make this hypermedia content dynamic, allowing
developers to provide more than simple, static `.html` files.

As a scripting language, on a scripting context, PHP
isolates every execution. Meaning that it doesn't share
memory or other resources among executions.

On the web context we have two different ways to execute
php code. Both presenting us with their pros and cons.

One can attach PHP to http servers using a CGI-like connection
OR as a module to http the server. The main difference between
both is that **http modules share resources with the HTTP server**
while as a **CGI, php has a fresh execution on every request**.

Using it as a module used to be very popular back in the days,
as the communication between the http server and code execution
has less friction. Meanwhile the CGI mode would, for example,
rely on network communication between http server and code
execution.

**This used to make CGI a bottleneck for PHP set ups. Nowadays,
this is exactly how PHP shines!**

With [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) a
web server like nginx or Apache can easily execute php code
as if it was a CLI script. Where every request is 100% isolated
from each other.

This also means that the HTTP Server can scale independently
from the PHP code executor. With our current techonology, this
**is amazing to enable vertical scaling**.

Using PHP with CGI enables you to quickly update your application
without taking down the whole http server. With current load
balancers trend this is not that critical, but worth mentioning.

Another great benefit on using PHP-FPM is that whenever a php
script crashes, only that request context is doomed. The rest
of the application keeps running normally, as no resources are
shared.

So if we just ignore the idea that we can use php within a
module connected to http servers, the way php works (with FPM)
is basically: **HTTP Server ⇨ PHP-FPM (Server) ⇨ PHP**.

That's why we often state that PHP is extremelly scalable
by nature.

But PHP is still a scripting language. And as in the CGI
context it has a fresh execution every single request, it
is also clear that PHP's most scalable trait is also one
of its most relevant performance bottlenecks.

### How does PHP scripting work?

PHP language is written in C and the way it works is actually
quite cool.

The php interpreter would read text files containing php code,
analyse its syntax, transform everything it understands
as php code into opcodes and later on execute this opcode list.

In simple terms, php will: **parse, compile and execute**.

Every single time (we'll come back to this point later).

So whenever you're attempting to execute a php script, you'll
face different things in different moments:

**Syntatic errors and language checks happen during the parsing
and compiling phase. Logical errors (like exceptions) occur
during execution phase only.**

The way PHP currently does this is by using an [Abstract Syntatic Tree](https://wiki.php.net/rfc/abstract_syntax_tree)
to figure out what the things inside a php file actually mean.

This syntatic tree maps language constructs to compiling
instructions, that when compiled turn into Zend VM opcodes.
Such opcodes are then to be [interpreted by the Zend VM](https://github.com/php/php-src/blob/master/Zend/zend_vm_def.h).

So, if you really think about, **the most relevant step for PHP
execution is actually letting Zend VM execute opcodes**. This is
what really produces our desired result.

In the end of the day **having a fresh execution on every
request doesn't seem that smart if we have to compile php
syntax into opcode every single time**.

That's why [OPcache](https://www.php.net/manual/en/intro.opcache.php)
exists. There are simply no solutions without problems.

With OPcache php can benefit from a shared memory space: read/store
already parsed script opcodes and boost future executions.

The first time a request hits `index.php`, for example, php parses,
compiles and executes it. Second time a request hits `index.php`, php
will simply fetch from opcache and execute it right away.

This is about to change, though. Since PHP 7.4 a [opcache preloader got added to php](https://wiki.php.net/rfc/preload).
This functionality allows a set of php files to be preloaded
during a sever start-up process. This way, the first time a request
hits `index.php`, php will just fetch it from opcache and execute it.
**No extra parsing needed**.

### Is this information even useful?

I have this feeling that php engineers (as well as python engineers)
usually hold great knowledge on how back-end components connect to
each other, given our languages usually don't provide much magic or
vendor black boxed packages.

I also feel like this is changing over time, as Open Source software
proved to be more than just a Hype, but a great economical model.
More languages are requiring engineers to understand how things come
together and becoming more vendor agnostic.

We are not quite there yet, and mastering PHP's ecosystem is a must
for properly investing efforts on performance and security. Knowing
where and when things happen will support you optimizing how they
should happen.

I hope this text helped you to better understand how PHP's engine
actually works behind the scenes, the tooling it uses and important
keywords you'll probably face at some point.

If you have questions I didn't answer here or believe I made any
mistake within this text, please feel free to reach me and, if I feel
it fits here, I'll keep this post updated so we can all learn
together.

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
  "headline": "How the php engine works internally",
  "description": "Let's do a quick overview on how php engine works both as a web engine and how the language behaves internally within the Zend VM.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/2-engine.jpg"
   ],
  "datePublished": "2019-11-09T00:00:00+08:00",
  "dateModified": "2020-02-29T00:00:00+08:00",
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
