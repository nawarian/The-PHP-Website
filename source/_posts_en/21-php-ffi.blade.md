---
isFeatured: true
slug: php-ffi
title: Complete guide to FFI in PHP
category: guide 
createdAt: 2021-03-11
sitemap:
  lastModified: 2021-03-11
image:
  url: /assets/images/posts/21-php-ffi-640.webp
  alt: 'A human shape painted with zeros and ones.'
tags:
  - ffi
  - binary
  - raylib
  - game
meta:
  description:
    By using FFI your PHP programs will be able to use
    libraries written in C, Rust, Golang or any other
    language capable of producing an ABI. Here's how!
  twitter:
    card: summary
    site: '@nawarian'
---

Before anything, I'd like to tell you that I started a short video series on this subject by attempting to implement a PHP bridge to the raylib library using FFI. It is in brazilian portuguese but I'm certain you can figure out the PHP code part, so below you'll find it embedded from lbry and you can also [click here if privacy isn't really your thing](https://youtu.be/wPbnjcvW-Tk).

<iframe style="margin: auto;" id="lbry-iframe" width="560" height="315" src="https://lbry.tv/$/embed/php_ffi_ola_mundo/a6959afa7affbba52bb31e68fbfbd222af129261?r=HRN5EEjNyXcykeZ8RjgLX697DuZvpA7g" allowfullscreen></iframe><br />

For the unlucky ones who can't understand portuguese yet, please keep reading for a nice overview on what FFI is and how you can master it with PHP!

## What is FFI and what can I do with it?

FFI or [Foreign Function Interface](https://en.wikipedia.org/wiki/Foreign_function_interface) is a technique that allows programs to use libraries written in different languages. It is much faster than RPC or APIs because you don't interface with the network and, instead, your program will interface directly with the binary definition of the program.

In other words, by using FFI your PHP programs will be able to use libraries written in C, Rust, Golang or any other language capable of producing an ABI.

FFI allows you to use libraries from compiled languages such as C, Rust and Golang. But it is not a magic tool that will allow two different runtimes to communicate with each other without a network.

By adopting FFI in your PHP you will be able to use any shared object you wish for your project: `.dll` for Windows, `.so` for Linux or `.dylib` for MacOS.

This gives you an opportunity to break out from the [PHP's Virtual Machine (Zend VM)](https://thephp.website/en/issue/php-8-jit/) and code almost anything you'd like using PHP. Using C libraries such as raylib or libui won't require you to depend on any C extensions ([like we did in my other post about using raylib to make games with PHP](https://thephp.website/en/issue/games-with-php/)).

## Will FFI make my code run faster?

You might be thinking that since you'll be using external code written in C it is potentially faster than in PHP. The line of thought isn't necessarily wrong, but we must keep in mind that languages don't do magic: they do what we tell them to do.

**When it comes to CPU time, calling external functions from PHP using FFI may cost you twice as much the time you'd need to perform the same operation in pure PHP.** That's because PHP's Virtual Machine is already very optimized and interfacing with external code requires a translation process that adds cost to your processing.

It is normal and all languages that support FFI that I've seen so far will perform less when using FFI.

**You may optimize your memory consumption!** As you can see from my post on [Mastering Bitwise Operations in PHP](https://thephp.website/en/issue/bitwise-php/#not-the-best-candidate), each PHP variable has an internal type zval and it does many things to make PHP's life easier such as representing every PHP Integer with type INT64. So even `0x10` would be stored as `0x0000000000000010` in PHP (and all other members of zval have their pointers allocated).

So a good practice is to find a balance between processing things using PHP and using FFI for handling objects in memory. Like this you can optimize memory consumption which may or not impact your overall CPU Time.

## FFI or C Extensions, which should you use?

FFI is often given as a tool for prototyping: you make your first steps with it and later on migrate to a native extension code.

I think if your code doesn't care much about performance (unlikely, but can happen) it is okay to use FFI just to extend PHP's capability. [Don't forget that FFIs in PHP are still experimental and you may face bugs or API changes in its core from time to time](https://www.php.net/manual/en/intro.ffi.php).

C Extensions should be normally written in C code, a scary barrier for many PHP engineers. But they integrate to PHP's Virtual Machine, so the extensions will be way faster than FFI because they call C code directly from C (no translations necessary) and map only code that will interface with the end-user.

Extensions are compiled against a specific PHP version, and this creates an annoying dependency that may slow you down from upgrading your PHP version. If you're up for upgrading the extension yourself and following the integration process its community proposes, that's even better but will still cost you a few days.

FFIs will always work out of the box and won't prevent you from upgrading PHP versions because the [FFI extension is part of PHP's core](https://github.com/php/php-src/tree/master/ext/ffi).

## Getting started with FFI: let's build a raylib window

One thing that PHP itself definitely can't do is to manipulate native windows on the operating system. There are extensions for this such as the [PHP-GTK](http://gtk.php.net/) and the [raylib extension we saw before](https://thephp.website/en/issue/games-with-php/), another option is to use FFIs.

I'll choose [Raylib](https://www.raylib.com/) for our example because its interface is very very simplified and pleasant to work with.

### Install raylib's shared object (library)

For Mac users this will be as simple as installing raylib via HomeBrew:

```
$ brew install raylib
```

There are complete guides on how to install it for other systems. Here you can find guides for [Installing on Windows](https://github.com/raysan5/raylib/wiki/Working-on-Windows) and [Installing on Linux](https://github.com/raysan5/raylib/wiki/Working-on-GNU-Linux).

After installing everything you should have a shared object available in your system. On MacOS you can see the `libraylib.dylib` file under `/usr/local/Cellar/raylib/<version>/lib`:

```
$ ls -la /usr/local/Cellar/raylib/3.5.0/lib
cmake			libraylib.351.dylib	libraylib.dylib
libraylib.3.5.0.dylib	libraylib.a		pkgconfig
```

On Windows you'll care about the `.dll` file and on GNU Linux you'll care about the `.so` file.

### Let's first prototype in C

The easiest way to understand if it works well in PHP with FFI is by understanding how it should behave with C in the first place, right?

So the first thing we will do is to build a simple program in C using raylib that will build our window. So let's create a `hello_raylib.c` file with the following content:

```
#include "raylib.h"

int main(void)
{
  Color white = { 255, 255, 255, 255 };
  Color red = { 255, 0, 0, 255 };

  InitWindow(
    800,
    600,
    "Hello raylib from C"
  );

  while (
    !WindowShouldClose()
  ) {
    ClearBackground(white);

    BeginDrawing();
      DrawText(
        "Hello raylib!",
        400,
        300,
        20,
        red
      );
    EndDrawing();
  }

  CloseWindow();
}
```

The above should create a window with 800x600 size and the "Hello raylib from C" text in the title bar. Inside this window, a text "Hello raylib!" with red color should appear with its origin at the middle of the screen.

Let's compile and run the above code:

```
$ gcc -o hello_raylib \
  hello_raylib.c -lraylib
$ ./hello_raylib
```

**Notice:** use the C compiler available for your platform. In my case I used `clang` but it should work more or less the same.

Below you see the expected result.

<figure style="text-align: center">
  <a href="/assets/images/posts/21-php-ffi/raylib-window-c.png" target="_blank">
    <img src="/assets/images/posts/21-php-ffi/raylib-window-c.png" alt="A native window with dimensions 800 by 600 with title 'Hello raylib from C' presenting a text in red color saying 'Hello raylib!'" />
  </a>
  <figcaption>
    A native window with dimensions 800 by 600 with title "Hello raylib from C"
    presenting a text in red color saying "Hello raylib!"
  </figcaption>
</figure>

### Now with PHP! Let's build a header file

To let PHP communicate with C (or other languages), we must first create an interface. In C such interface is represented by header files. That's exactly why most `.c` files have a correspondent `.h` file in the codebase: it outlines common objects and function signatures that files linking to it might find useful.

Since we want to reference `libraylib.dylib` the first line of our header file will contain the following define, specific for FFI. So let's start writing our `raylib.h` file that will interface with the PHP code:

```
#define FFI_LIB "libraylib.dylib"
```

**Notice:** the referenced file may change according to your operating system.

Raylib has many many functions, which [you can check at their cheatsheet](https://www.raylib.com/cheatsheet/cheatsheet.html). But we don't need to import all of them. In fact, I recommend you to import only the ones necessary for your program. In our case, we need only 7:

```
#define FFI_LIB "libraylib.dylib"

void InitWindow(
  int width,
  int height,
  const char *title
);
bool WindowShouldClose(void);
void ClearBackground(
  Color color
);
void BeginDrawing(void);
void DrawText(
  const char *text,
  int x,
  int y,
  int size,
  Color color
);
void EndDrawing(void);
void CloseWindow(void);
```

Notice that some of the function signatures require very specific types that are built by raylib. The functions `ClearBackground` and `DrawText` require an argument of type `Color`, which we also need to import. So let's add it to our header file:

```
#define FFI_LIB "libraylib.dylib"

typedef struct Color {
  unsigned char r;
  unsigned char g;
  unsigned char b;
  unsigned char a;
} Color;

void InitWindow(int width, int height, const char *title);
// ...
```

Our **raylib.h** file is ready to be used by PHP now.

### Load this header into PHP

Since we have a header file we may import it by using the [FFI::load()](https://www.php.net/manual/en/ffi.load.php) function like this:

```
<?php

$ffi = FFI::load(
  __DIR__ . '/raylib.h'
);
```

Using this `$ffi` object we can now mimic the previous C code. Let's build the `white` and `red` variables of type `Color`:

```
<?php

$ffi = FFI::load(__DIR__ . '/raylib.h');

$white = $ffi->new('Color');
$white->r = 255;
$white->g = 255;
$white->b = 255;
$white->a = 255;

$red = $ffi->new('Color');
$red->r = 255;
$red->a = 255;
```

By default all fields of a struct will be initialized with a zero value. In the case of `unsigned char` (which varies from 0 to 255) the zero value is an integer `0`.

Now we can easily build our window and draw on the screen:

```
<?php

$ffi = FFI::load(__DIR__ . '/raylib.h');

// ...

$ffi->InitWindow(
  800,
  600,
  "Hello raylib from PHP"
);

while (
  !$ffi->WindowShouldClose()
) {
  $ffi->ClearBackground(
    $white
  );

  $ffi->BeginDrawing();
    $ffi->DrawText(
      "Hello raylib!",
      400,
      300,
      20,
      $red
    );
  $ffi->EndDrawing();
}

$ffi->CloseWindow();
```

### We have our raylib window using PHP

As you probably realized, all C functions defined in `raylib.h` can be used in PHP by referencing them with our `$ffi` object. C variables are then mapped to PHP variables vice-and-versa.

Our final PHP file and its result looks like the following:

```
<?php

$ffi = FFI::load(__DIR__ . '/raylib.h');

$white = $ffi->new('Color');
$white->r = 255;
$white->g = 255;
$white->b = 255;
$white->a = 255;

$red = $ffi->new('Color');
$red->r = 255;
$red->a = 255;

$ffi->InitWindow(800, 600, "Hello raylib from PHP");
while (!$ffi->WindowShouldClose()) {
  $ffi->ClearBackground($white);

  $ffi->BeginDrawing();
    $ffi->DrawText("Hello raylib!", 400, 300, 20, $red);
  $ffi->EndDrawing();
}

$ffi->CloseWindow();
```

<figure style="text-align: center">
  <a href="/assets/images/posts/21-php-ffi/raylib-window-php-ffi.png" target="_blank">
    <img src="/assets/images/posts/21-php-ffi/raylib-window-php-ffi.png" alt="A native window with dimensions 800 by 600 with title 'Hello raylib from PHP' presenting a text in red color saying 'Hello raylib!'" />
  </a>
  <figcaption>
    A native window with dimensions 800 by 600 with title "Hello raylib from PHP"
    presenting a text in red color saying "Hello raylib!"
  </figcaption>
</figure>

## Common issues with FFI and how to solve them

I've been playing around with FFI to come up with nice bindings for Raylib for PHP and faced some issues, knowing about them and how to overcome such issues may also be helpful for you!

My biggest tip is: don't mix your application code with FFI code, please extract your bindings into a standalone library and require it using composer. This will not solve most of your issues, but will definitely isolate them and make it easier to test.

### FFI can be difficult to test

In the case of Raylib specifically we can't test much. Mostly because it manipulates native windows and PHP has no easy way to perform assertions of this kind.

So keep in mind that if you're writing something really outside PHP's regular scope, you'll need other tools for testing. Also make sure that such tools can run in all platforms possible.

For example, it is possible to capture a window PID by searching for its title with xorg, and I know that somewhere the Windows API also gives you this capability. **If you want to test, you will probably have to give up on keeping your project PHP only**.

It is also valuable to remember that tests don't necessarily add value everywhere in your application. [I use tests as a learning tool so I have a safe environment for learning new concepts little by little](https://thephp.website/en/issue/tdl-test-driven-learning-framework/) without caring about different dependencies all at once and, unfortunately, most PHP testing frameworks didn't help me achieving this while learning Raylib. My solution in this case is to create different PHP files that are supposed to do a single thing, just like test cases.

### Hard to perform static analysis

I didn't find a nice way to overcome this issue. Static Analysis tools such as [psalm](https://psalm.dev/) go пиздец with FFI code! (Practicing my Russian skills too)

Back to the `$white` and `$red` snippet, let's see why:

```
$white = $ffi->new('Color');
$white->r = 255;
$white->g = 255;
$white->b = 255;
$white->a = 255;
```

If you check [FFI::new() signature](https://www.php.net/manual/en/ffi.new.php) you'll learn that it returns `FFI\CData` or `null`. This CData return type is an object which should contain all fields from the struct being used.

As far as I know, psalm has no way to annotate that the variable `$white` contains the four integer fields `$r`, `$g`, `$b` and `$a`. And psalm can't even know they exist because, well, they're written in C somewhere else!

So ideally you'd abstract away the FFI logic into some sort of Facade or Adapter class, which you will promise to cover with tests as much as possible, and let psalm ignore this specific class when performing static analysis.

This Facade/Adapter would then map PHP values (primitives or objects) into CData properly and take care of the C function calls for you.

You'll be more or less building a PHP library, which is ideal if you think about it! This way you prevent your production code from being polluted with FFI-specific logic, and things get naturally testable for the application side.

### Keep your library up to date

One big benefit of using FFI over PHP Extensions is that you won't have to upgrade your C code for every new PHP Version. But you still need to manage C library versions.

I recommend you to learn the original library's versioning system and tag your php bindings accordingly, except for patch versions. So major and minor versions will always match the original C library, while you'll still have the freedom to bump patch versions whenever you fix bugs and such.

This naturally pushes you to respect 100% the original C library interfaces. But leaves you free to pull and distribute security fixes and bug fixes both in the C library and your PHP code.

### The multi-platform problem

PHP is multi-platform. Its users expect all libraries to be multiplatform too. Keeping this premisse can be tricky when handling FFI code.

Back to the raylib example, importing that shared file already forces us to choose by filename: `raylib.so` (GNU Linux), `libraylib.dylib` (MacOS) or `raylib.dll` (Windows). Import the wrong file and your library simply won't work!

You may write different header files, specific for the platform. This will create lots of duplication, but helps a bit.

Another option is to use [FFI::cdef()](https://www.php.net/manual/en/ffi.cdef.php) to load your function signatures. It is very similar to FFI::load() but expects a raw string instead of a file path. In this case you can craft your shared object file path in runtime.

You may detect the Operating System running your php code by calling the [php_uname()](https://www.php.net/manual/en/function.php-uname.php) function. Avoid using the `PHP_OS` constant: it shows you the OS information from the computer that compiled your PHP binary, which in some cases may not be the same that is effectively running your code.

Last but not least, please consider that some libraries aren't multi-platform. Porting them to PHP could be frustrating for many end users and, if you decide to port such a library anyways, please consider throwing exceptions for unsupported operating systems: this will tell users right away what the problems are.

### There are bugs in the FFI extension itself

Remember, FFI is experimental! You may find unexpected bugs at any moment!

Whenever you face mind boggling errors with your FFI integration, always make sure you create an equivalent C code to assert that what you're doing should work before suspecting FFI's behaviour.

If suspicions are correct, [please file a bug to the PHP team](https://bugs.php.net/). I'm not sure they will be happy about it, but you'll certainly help the community to grow.

I found one recently and I'm even trying to implement a fix myself, it will be a nice project and potentially my next post too. So stay tuned! :)

## Closing thoughts

I got very excited to work with FFI and I hope this post helped you bootstrapping your FFI set up too!

Little by little I'm getting more used with low-level code and has been FFI a great opportunity for me to program different use cases (such as game development or audio processing) in a language I love (PHP).

Please keep in mind that PHP is an Open Source language and its community depends on contributions from people like you. You can  use your knowledge to give back to the community by reporting bugs, fixing them, filling documentation gaps you find on the way or writing articles like this one. And FFI is definitely an area of knowledge that needs love and care not to be forgotten.

See you next time!

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
  "headline": "Complete guide to FFI in PHP",
  "description": "By using FFI your PHP programs will be able to use libraries written in C, Rust, Golang or any other language capable of producing an ABI. Here's how!",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/21-php-ffi-640.webp"
   ],
  "datePublished": "2021-03-11T00:00:00+08:00",
  "dateModified": "2021-03-11T00:00:00+08:00",
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
