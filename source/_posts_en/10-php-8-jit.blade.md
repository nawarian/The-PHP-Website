---
slug: php-8-jit
title: Understanding PHP 8's JIT
category: walkthrough
createdAt: 2020-03-03
sitemap:
  lastModified: 2020-03-03
image:
  url: /assets/images/posts/10-php-8-jit-640.webp
  alt: 'A number eight represented with engine chains.'
tags:
  - core
  - curiosity
  - php8
  - version
meta:
  description:
    PHP 8’s Just In Time compiler is implemented as part of the Opcache
    extension and aims to compile some Opcodes into CPU instructions in
    runtime. Let's understand how it works all together.
  twitter:
    card: summary
    site: '@nawarian'
---

[Versão em português em andamento...](#)

## TL;DR
PHP 8’s Just In Time compiler is implemented as part of the [Opcache extension](https://www.php.net/manual/en/book.opcache.php) and aims to compile some Opcodes into CPU instructions in runtime.

This means that **with JIT some Opcodes won’t need to be interpreted by Zend VM and such instructions will be executed directly as CPU level instructions.**

## PHP 8’s JIT
One of the most commented features PHP 8 will bring is the Just In Time (JIT) compiler. Many blogs and community are talking about it and for sure a big buzz is around, but I’ve found so far very little details about what JIT is supposed to do in details.

After researching and giving up many times, I decided to check the PHP source code myself. Aligning my little knowledge on C language and all the scattered information I’ve collected so far I came up with this post and I hope it helps you understanding PHP’s JIT better as well.

**Oversimplifying things: when JIT works as intended, your code won’t be executed through Zend VM and will, instead, be executed directly as a set of CPU level instructions.**

That’s the whole idea.

But to understand it better, we need to think about how php works internally. Is not very complicated, but requires some introduction.

I wrote a blog post with a rough [overview on how php works](/en/issue/how-does-php-engine-actually-work/). If you think this post here is getting too dense, just check the other one out and come back later. Things will make sense more easily.

## How PHP code is executed?
We all know php is an interpreted language. But what does it really mean?

Whenever you want to execute PHP code, being that a snippet or an entire web application, you’ll have to go through a php interpreter. The most commonly used ones are PHP FPM and the CLI interpreter.

Their job is very straight forward: receive a php code, interpret it and spit back the result.

This normally happens to every interpreted language. Some might remove some steps, but the overall idea is the same. In PHP it happens like this:

1. PHP code is read and transformed into a set of keywords known as Tokens. This process allows the interpreter to understand what piece of code is written in which part of the program. **This first step is called Lexing or Tokenizing.**

1. With tokens in hands, the PHP interpreter will analyze this collection of tokens and try to make sense out of them. As result an Abstract Syntax Tree (AST) is generated through a process called **parsing**. This AST is a set of nodes indicating what operations should be executed. For example, “echo 1 + 1” should in fact mean “print the result of 1 + 1” or more realistically “print an operation, the operation is 1 + 1”.

1. With the AST in hands it is much easier to understand operations and precedence, for example. Transforming this tree into something that can be executed requires an intermediate representation (IR) which in PHP we call Opcode. The process of transforming an AST into Opcodes is called compilation.

1. Now, with Opcodes in hands comes the fun part: **executing** the code! PHP has an engine called Zend VM, which is capable of receiving a list of Opcodes and execute them. After executing all Opcodes the Zend VM exists and the program is terminated.

I have a diagram to make it a bit clearer for you:

<figure style="text-align: center">
  <a href="/assets/images/posts/10-php-8-jit/zendvm-no-opcache.png" target="_blank">
    <img src="/assets/images/posts/10-php-8-jit/zendvm-no-opcache.png" alt="The PHP's interpreting flow." />
  </a>
  <figcaption>A simplified overview on PHP's interpreting flow.</figcaption>
</figure>

Quite straight forward, as you can perceive. There’s a bottleneck here though: what’s the point of lexing and parsing the code every time you execute it if your php code might not change that often?

In the end we only care about Opcodes, right? Right! That’s why **Opcache extension** exists.

## The Opcache extension

The Opcache extension is shipped with PHP and generally there’s no big reason to deactivate it. If you use PHP, you should probably have Opcache switched on.

What it does is to add a in memory shared cache layer for Opcodes. Its job is to take those Opcodes freshly generated out of our AST and cache them so further executions can easily skip the lexing and parsing phases.

Here’s a diagram with this flow considering the Opcache extension:

<figure style="text-align: center">
  <a href="/assets/images/posts/10-php-8-jit/zendvm-opcache.png" target="_blank">
    <img src="/assets/images/posts/10-php-8-jit/zendvm-opcache.png" alt="The PHP's interpreting flow with Opcache" />
  </a>
  <figcaption>The PHP's interpreting flow with Opcache. If a file was already parsed, php fetches the cached Opcodes for it instead of parsing all over again.</figcaption>
</figure>

Amazing to see how it beautifully skips the Lexing, Parsing and Compiling steps 😍.

**Side note:** this is where [PHP 7.4’s preloading feature](https://wiki.php.net/rfc/preload) shines! It allows you to tell PHP FPM to parse your codebase, transform it into Opcodes and cache them even before you execute anything.

You might be wondering where JIT comes in, right?! I hope so, that’s why I’m writing this article afterall…

## What the Just In Time compiler effectively does?

After listening to Zeev’s explanation in the [PHP and JIT podcast episode from PHP Internals News](https://phpinternals.news/7) I managed to get some idea on what JIT is actually supposed to do…

If Opcache makes it faster to obtain Opcodes so they can go directly to Zend VM, JIT is supposed to make them run without the Zend VM at all.

The Zend VM is a program written in C that act as a layer between Opcodes and the CPU itself. **What JIT does is to generate compiled code in runtime so php can skip the Zend VM and go directly to CPU.** Theoretically we should gain performance from it.

This sounded weird to me at first, because in order to compile machine code you need to write a very specific implementation for each type of architecture. But in fact it is quite plausible.

PHP’s JIT implementation uses a library called [DynASM (Dynamic Assembler)](https://luajit.org/dynasm.html) which maps a set of CPU instructions in one specific format into assembly code for many different CPU types. So the Just In Time compiler transforms Opcodes into an architecture-specific machine code using DynASM.

One thought bugged me a lot for quite a while, though…

**If preloading is capable of parsing php code into Opcodes before execution and DynASM can compile Opcodes into Machine Code (Just In Time compilation), why the hell don’t we compile PHP right away using Ahead of Time compilation?!**

One of the clues I had from listening to Zeev’s episode was that PHP is weakly typed, meaning that often PHP does not know what type a variable has until Zend VM attempts to execute a certain Opcode.

This can be perceived by looking at the [zend_value union type](https://github.com/php/php-src/blob/43443857b74503246ee4ca25859b302ed0ebc078/Zend/zend_types.h#L282-L300), which has many pointers to different type representations to a variable. Whenever the Zend VM tries to fetch the value from a zend_value, it uses macros like the [ZSTR_VAL](https://github.com/php/php-src/blob/43443857b74503246ee4ca25859b302ed0ebc078/Zend/zend_types.h#L794) that attempts to access the string pointer from the value union.

For example, [this Zend VM handler](https://github.com/php/php-src/blob/43443857b74503246ee4ca25859b302ed0ebc078/Zend/zend_vm_def.h#L722-L767) is supposed to handle a “Smaller or Equal Than” (<=) expression. Look how it branches into many different code paths just to guess the operand types.

**Duplicating such logic of type inference with Machine Code is unfeasible and could potentially make things even slower.**

Compiling everything after types got evaluated is also not a great option, because compiling to machine code is a CPU intensive task. So compiling EVERYTHING in runtime is also bad.

## How the Just In Time compiler behaves?

Now we know that we can’t infer types to generate a good enough ahead of time compilation. We also know that compiling in runtime is expensive. How can be JIT beneficial to PHP?

In order to balance this equation, PHP’s JIT tries to compile only a few Opcodes that it considers the effort could pay off. To do so, **it profiles Opcodes being executed by the Zend VM and checks which ones might make sense to compile. (based on your configuration)**

When a certain Opcode is compiled, it will then delegate the execution to this compiled code instead of delegating to the Zend VM. Looks like the following:

<figure style="text-align: center">
  <a href="/assets/images/posts/10-php-8-jit/zendvm-opcache-jit.png" target="_blank">
    <img src="/assets/images/posts/10-php-8-jit/zendvm-opcache-jit.png" alt="The PHP's interpreting flow with JIT" />
  </a>
  <figcaption>The PHP's interpreting flow with JIT. If compiled, Opcodes don't execute through the Zend VM.</figcaption>
</figure>

So in the Opcache extension there are a couple of instructions detecting if a certain Opcode should be compiled or not. If yes, the compiler then transform this Opcode into machine code using DynASM and executes this newly generated machine code.

The interesting thing is that since there's a limit in megabytes for compiled code in the current implementation (also configurable), the code execution must be able to switch between JIT and interpreted code seamlessly.

By the way, [this talk from Benoit Jacquemont on php’s JIT](https://afup.org/talks/3015-php-8-et-just-in-time-compilation) helped me A LOT understanding this whole thing.

I’m still unsure about when the compilation part effectively takes place, but I think I don’t really wanna know for now.

## So probably your performance gains won’t be huge

I hope right now it is much clearer WHY everyone is saying most of php applications won’t receive big performance benefits from using the Just In Time compiler. And why Zeev’s recommendation of profiling and experiment different JIT configurations for your application is the best way to go.

The compiled Opcodes will be normally shared among multiple requests if you are using PHP FPM, but this is still not a game changer.

That’s because JIT optimizes CPU-bound operations, and most php applications nowadays are more I/O bound than anything. Doesn’t matter if the processing operations are compiled if you’ll have to access disk or network anyways. Timings will be very similar.

**Unless…**

You’re doing something not I/O bound, like image processing or machine learning. Anything not touching I/O will benefit from the Just In Time compiler.

That’s also the reason why people are now saying we’re closer to write native PHP functions written in PHP, instead of C. The overhead won’t be expressive if such functions are compiled anyways.

Interesting times to be a PHP programmer…

---

I hope this article was useful for you and that you managed to grasp better what the PHP 8’s JIT is about.

Feel free to reach me out on twitter if you’d like to add something I might have forgotten here and don’t forget sharing this with your fellow developers, it surely will add much value to your conversations!


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
  "headline": "Understanding PHP 8's JIT",
  "description": "PHP 8’s Just In Time compiler is implemented as part of the Opcache extension and aims to compile some Opcodes into CPU instructions in runtime. Let's understand how it works all together.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/10-php-8-jit-640.webp"
   ],
  "datePublished": "2020-03-03T00:00:00+08:00",
  "dateModified": "2020-03-03T00:00:00+08:00",
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
