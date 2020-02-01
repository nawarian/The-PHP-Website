---
slug: tdl-framework
title: TDL, a (programming language) learning framework
createdAt: 2020-01-31
sitemap:
  lastModified: 2020-01-31
image:
  url: /assets/images/posts/6-tdl-framework-640.webp
  alt: 'A half opened chest'
meta:
  description:
    Learning a new (programming) language is an extremely necessary
    skill for any fullstack engineer. Being a fullstack myself,
    as most PHP programmers are, I came up with my own framework
    to make it rational.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/framework-tdl/)

## TL;DR

TDL = Test-Driven Learning.

If you could take one thing with you from this text, take this:
**Test-Driven Development can be used for learning too.**
[Learn how to TDD efficiently](/en/issue/real-life-tdd-php/) and you'll
grasp the main concept of this whole text.

---

Recently I started (again) the most exciting and recurring quest
we programmers face from time to time:
**✨ learning a new programming language ✨.**

This time I've decided to learn [Rust](https://www.rust-lang.org/),
which is (still) crazy interesting to me, as I'm mostly a web developer
and never touched anything systems related besides the php source code.

After doing this whole process multiple times with different languages,
I started to see patterns in my learning process. It took me a good mix
of self-awareness, project management skills (yes) and research to
compile such patterns into a model that I can easily integrate to my
daily life.

I believe that this time, learning Rust, I came to a more or less
stable version of my learning framework based on TDL. Even though
optimizing this framework is a never-ending process.

To be clear, **I don't intend to state this is the ultimate way
of learning a new programming language or anything similar.**
You'll have to figure out your best way of learning by yourself.

As usual, let's switch on the "no bs mode" and get started!

---

Each section here starts with a list of topics so you can know the main
thoughts in beforehand. Feel free to jump to the next heading if the
bullet points here don't catch your attention.

This text is divided in three parts and, honestly,
I could write a book just by developing all thoughts
they present. ([Ping me on twitter if you're interested in this book, btw](https://twitter.com/intent/tweet?text=Write+the+freaking+book+@nawarian!!+-+https://thephp.website))

1. "**Getting started with a new programming language**" presents a mental
model on how to discover a programming language and how to earn our
most essential tool for learning with TDL: testing.

2. "**How does Test-Driven Learning (TDL) work?**" will, hopefully, explain
how this thing works, how to keep the feedback loop and so on.

3. Last but not least comes "**Praxis: The hardest part is discovering**",
which brings some tips on how to feed the first step of TDL's loop.

## Getting started with a new programming language

Topics in this section:

- All programming languages are similar
- Every language has a purpose
- Finding the language's highlights
- How to test in this language

If you already know which programming language you want to learn
the bootstraping process is fairly simple. I have a weird advice
for you, though:

**Avoid writing any code in it before you have a clear mental model
on what this language is capable of.**

To build such mental model is always good to remember that programming
languages are extremely similar to one another and they usually can do
something(s) very well.

### All programming languages are similar

**Most programming languages are very similar to each other.** They
might have different structures, syntax, conventions but in the end
of the day most of them will present you with a stack-based
execution where you **store variables, load values into them, execute
expressions, call functions and so on.**

Imperative or declarative, compiled or interpreted, **they all share
common characteristics.** The sooner your grasp such characteristics,
the closer to be a matter of syntax they'll will feel like.

Beyond the syntax you'll face models that are bound to the language.
They usually show up once you start understanding what this language
is made for.

So **don't start coding yet!** Before writing any line of code, make
sure you took a look on the language and know how it looks like and what
is specific to this language that makes it singular and useful.

Usually **a language differs itself from another by having a purpose.**

### Every language has a purpose

[PHP was a set of CGI scripts for templating](/en/issue/how-does-php-engine-actually-work/),
JavaScript was born to make web pages interactive, ActionScript to
make flash programs interactive and extensible, C for zero-cost
abstraction on systems development, Rust for memory safety on systems
development, Ruby to write code like books, LLVM's IR to make
easier to create new programming languages...

Every programming language has (or had) a purpose when it was first
written.

**Knowing a language's purpose helps you reason how it works.**

Once you have this piece of information the best features such
language can present to you will start appearing to you.

### Finding the language's highlights

Let's take Rust as an example here, since it is my freshest memory.

I understood that Rust is a systems programming language with big
focus on memory safety while being fast. But how does this translate
to the language?

Whoof! Extremely harsh compile checks, variables ownership, borrowing,
pattern matching, never-nullable types and have I mentioned the compiler?

Knowing such things will both teach you what you should be proud of
knowing later on, but also what you need to achieve.

This will also let others believe you're actually learning this language.
There's nothing more frustating than receiving a "why" question you
can't answer in a satisfiable manner.

### How to test in this language

Still **before coding anything** in this language, the last step on
building this mental model around it is to learn how to write tests
in it.

Yes! Even before you learn how to declare a variable, learn how to
test. Or better phrased, **learn to perform assertions**.

PHP and C, for example, comes with the `assert()` function. Rust has
built-in `assert_*` macros and integrated tests tool.

Learn what is available for testing: is there a way to write unit tests?
Integration tests? Can I use a testing language like Gherkin with it?

**Spoiler alert:** Gherkin will be a great teacher during our next steps.

I'll take a rust program as example here on how to write an essential
assertion so we can start our TDL loop:

```rust
// src/main.rs

#[test]
fn test_basics() {
  assert_eq!(1, 1); // 1 = 1
  assert!(true); // success
  assert!(false); // failure
}
```

Then I just run `$ cargo test` and DONE! I know the basics on
how to perform assertions on my new language. Time to start
learning!

---

## How does Test-Driven Learning (TDL) work?

Topics in this section:

- Introduction with code examples
- Discover
- Assert
- Learn
- Repeat

Could be that you're thinking I'm trying to create a new fake
trend here or something. But [Test-Driven Learning is really a thing](https://digitalcommons.calpoly.edu/csse_fac/88/).

It is important to notice that TDL in this research above mentioned
is just a tool for teaching. What I'm presenting here is my use of
such tool in a framework I've built and adopted.

I base my learning with TDL on three repeatable steps:
**discover, assert and learn**. And all these tools I've mentioned
follow the same formula: show someting new (discover), give feedback
on your failures (assert) and reward you on completion (learn).

Tools like Khan Academy, Vim Adventures and other MOOCs use approaches
that somehow fit in these steps.

The main idea is to first **discover** something you want or need
to learn, perform **assertions** in order to understand when you're
done. Make the assertions pass with what you **learned** or need to
learn.

Taking a very silly example with rust. I found out that I can set
variables. How does it work? Let's assert a variable `nawarian`
should contain a value 10.

```rust
// src/main.rs

#[test]
fn test_variables() {
  assert_eq!(10, nawarian);
}
```

I know now that I'll only attempt to discover new things once this
assertion passes. I'll keep bashing my keyboard until `cargo test`
is green!!

A little research here and there, the following seems to work:

```rust
// src/main.rs

#[test]
fn test_variables() {
  let nawarian = 10;

  assert_eq!(10, nawarian);
} 
```

Tests are green. I can either continue or play with this same one.
Instead of setting `nawarian = 10` I could do it with a loop, no?

Then how should I loop from 0 to 10 and increment **nawarian** every iteration?

```rust
// src/main.rs

#[test]
fn test_variables() {
  let nawarian = 0;
  for i in 0..10 {
    nawarian += 1;
  }

  assert_eq!(10, nawarian);
}
```

Things like the following will pop on my screen:

> **warning:** unused variable: `i`
>
> **help:** consider prefixing with an underscore: `_i`
>
> **error[E0384]:** cannot assign twice to immutable variable `nawarian`
>
> **help:** make this binding mutable: `mut nawarian`

Doesn't work... the compiler says `nawarian` is not mutable and even
complains that `i` is never used, that maybe I should prefix it with
`_` so nobody cares.

> **Me:** Heh!? Apparently variables are immutable by default in Rust.
> Interesting... I can make it mutable by using this `mut` keyword...

Write, run. IT WORKS!

**This structure is amazing because you understand where you stand.**
If you don't, you can always
do a quick search on this tiny specific issue you're facing right now.

This feedback loop will force you to **learn actively** instead of
passively: find a feature, try it out, seek the answer until
you're satisfied. Discover, assert, learn.

Another great thing is that passing tests make people happy. Seeing
how much you can refactor a code without breaking the tests feels
reeeally good!

I'm not saying you should, but with this simple assertion you can learn
from variables to threading. One step each time, keep tests green and
feed your feedback loop!

I'll try to give you some more information on how to handle each step
while using TDL:

### Discover

The discovery step is great to learn about syntax, language features,
packages, frameworks... Basically everything that catches your interest
can fit here.

Just make sure you keep your assertions simple. **It is extremely important
that you understand your test** much more than the actual code making it
pass. Tests must be clear, code you can always refactor.

### Assert

The assertion step might differ depending on which level you already
reached in your language.

Newbies (like me with Rust) should keep unit testing as much as possible,
using native language constructs like `assert`, `echo` or even `exit`.

Once you get more advanced, big and complex tests using Given, When, Then
structures might fit better. You'll need to learn how to use tools like
Gherkin or something as broad and descriptive within your language.

### Learn

Make your test pass!

Read the errors presented to you, understand your problem and once you're
done with understand which problem you need to solve: read the language
manual, search in different engines, ask friends or people who know more,

### Repeat

Go back to discovery step and find something new to assert and learn.

O simply be creative. That simple `nawarian = 10` could be transformed
into I/O operations, structs usage, FFI calls, threads...

**There's no code you can't make more complicated! 😉**

---

## Praxis: The hardest part is discovering

Topics in this section:

- Introduction about community and tools
- Get involved with the language
- Connect with the community
- Explain something you don't know

Theory sounds great, right? (maybe not even...)

But I know how hard it is in the beginning to grasp and take profit out
of this framework. To me the hardest part is feeding my loop with
more discoveries.

Luckily the best answer I've found so far in most languages/frameworks
I adopt this framework is: Community!

Rust, for example, has this amazing project from its community called
"[rustlings](https://github.com/rust-lang/rustlings)". It guides you
through tests written by the community and helps you on making them
compile, logical tests pass and so on.

[For php there's the PHP School](https://www.phpschool.io/)
which is very similar to rustlings, but is very extensible and has
many community contributed courses about specific modules/features.

But after you learn the syntax you'll need much more. You'll need to
see what and how the language is evolving.

### Get involved with the language

This is the time to watch talks, go to conferences and watch
people coding for hours in front of a camera...

Start contributing to open source projects too! This is a very
easy way to get proficient in your language: contribute, fail code
reviews and fix based on feedback from other people...

### Connect with the community

Also listen to podcasts, read blogs (or start one!), join reddits.
You will learn more and more what people are doing with this
language, what is considered normal and what is not.

Find local meetups, or even create one yourself. Talk to real people
about real problems.

### Explain something you don't know

Browse on stackoverflow, github issues or reddit forums for questions
you trully don't know how to answer. Find them, research and
answer (or at least try).

Sometimes you will be able to explain things with assertions, sometimes
you'll need broader view over things. Try both!

At some point you'll be browsing job opportunities for such language
and requirements will simply be matching. The ones that aren't, you'll
feed to your discovery and keep moving forward.

---

I'm glad you made until here, because I'm not trying to BS you. My
learning process so far feels quite painful but extremely useful to
me.

It also saves me lots of time by guiding me through short questions
and answers I need to provide, as I don't have time to invest on
a new language as I used to have a couple of years ago ~ sighs.

I truly hope this text was useful to you.

As always, feel free to send  me any sort of feedback by pinging me on twitter.

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
  "headline": "TDL, a (programming language) learning framework",
  "description": "Learning a new (programming) language is an extremely necessary skill for any fullstack engineer. Being a fullstack myself, as most PHP programmers are, I came up with my own framework to make it rational.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/6-tdl-framework-640.webp"
   ],
  "datePublished": "2020-01-31T00:00:00+08:00",
  "dateModified": "2020-01-31T00:00:00+08:00",
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

