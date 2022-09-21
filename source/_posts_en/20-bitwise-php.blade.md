---
slug: bitwise-php
title: Mastering binary and bitwise in PHP
category: walkthrough
createdAt: 2021-01-07
sitemap:
  lastModified: 2021-01-07
image:
  url: /assets/images/posts/20-bitwise-php/cover-640.webp
  alt: 'A human shape painted with zeros and ones.'
tags:
  - curiosity
  - binary
  - serialization
meta:
  description:
    I recently caught myself working on different
    projects that required me to rely heavily on bitwise
    operations in PHP. From reading binary files to emulating
    processors, this is a very useful knowledge to have and
    a very cool one too.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/operacoes-bitwise-php/)

I recently caught myself working on different projects that required
me to rely heavily on bitwise operations in PHP. From reading binary
files to emulating processors, this is a very useful knowledge to have
and a very cool one too.

PHP has many tools to support you with manipulating binary data, but I
must warn you from the beginning: if you're seeking ultra low-level
efficiency, this isn't the language for you.

Bare with me, though! **In this post I'll show you very valuable things
about bitwise operations, binary and hexadecimal handling that will be
useful for you in ANY language.**

This article grew quit a bit, so I'll leave here a quick summary so you
can easily navigate to the sections you'd like.

- [Why PHP might not be the best candidate](#not-the-best-candidate)
- [Quick introduction to binary and hexadecimal data representations](#introduction-to-binary-representation)
- [Carry operations](#carry-operations)
- [Data representation in computer memory](#memory-representation)
- [Arithmetic Overflows](#arithmetic-overflows)
- [Binary numbers and strings in PHP](#binary-numbers-and-strings)
- [Binary: Integers or Strings, which to use in PHP?](#integers-or-strings)
- [Debugging binary values in PHP](#debugging-binary-in-php)
- [Visualizing binary strings](#visualizing-binary-strings)
- [Bitwise Operations](#bitwise-operations)
- [What is a bitmask](#what-is-bitmask)
- [Normalizing integers](#normalizing-integers)
- [Conclusion and examples](#conclusion-examples)

## Why PHP might not be the best candidate { #not-the-best-candidate }

Look. I love PHP, ok? Don't get me wrong. And I'm sure it will handle
gracefully many more cases than you can imagine. But in cases where
you need to be very efficient while handling binary data, PHP simply
won't do the job.

Just to be clear: I'm not talking about how an application might
consume 5 or 10mb more, I'm talking about allocating the exact amount
of memory necessary to hold a certain data type.

According to the
[official documentation on integers](https://www.php.net/manual/en/language.types.integer.php)
, PHP represents decimals as well as hexadecimals, octals and binaries
with the type integer. So it doesn't really matter what data you put in
there, it will always be an integer.

You probably heard of _ZVAL_ before, it is this C struct that represents
every PHP variable. It has
[a field to represent all integers called zend_long](https://github.com/php/php-src/blob/da0663a337b608a4b0008672b494e3a71e6e4cfc/Zend/zend_types.h#L286).
As you can see, zend_long is of type _lval_ which has a platform-dependent
size: On 64-bit platforms
[it will be represented as a 64bit integer](https://github.com/php/php-src/blob/74f3bfc6eb7ec80287178e46bd5c269fd371ce5a/Zend/zend_long.h#L30-L31),
while [32-bit platforms represent it as a 32bit integer](https://github.com/php/php-src/blob/74f3bfc6eb7ec80287178e46bd5c269fd371ce5a/Zend/zend_long.h#L40-L41).

```c
# zval stores every integer as a lval
typedef union _zend_value {
  zend_long lval;
  // ...
} zend_value;

# lval is a 32 or 64-bit integer
#ifdef ZEND_ENABLE_ZVAL_LONG64
 typedef int64_t zend_long;
 // ...
#else
 typedef int32_t zend_long;
 // ...
#endif
```

Bottomline is: doesn't matter if you need to store `0xff`, `0xffff`,
`0xffffff` or whatever. They will all be stored as long (_lval_) with
32 or 64 bits in PHP.

I recently played around, for example, with microcontrollers emulation.
And while handling memory and operations properly is a must, I didn't
really need so much memory efficiency there because my host machine
compensates it in orders of magnitude.

Of course everything changes when you talk about C Extensions or FFI,
but that's not my point. I'm talking about pure PHP.

So keep this in mind: it works and it can achieve all behaviour you'd
like it to achieve, but types won't fit efficiently in most cases.

## Quick introduction to binary and hexadecimal data representations { #introduction-to-binary-representation }

Look, before we talk about how PHP handles binary data we must detour
a little and talk about binary stuff first. If you think you already
know everything you need about this, just jump to the
[Binary numbers and strings in PHP](#binary-numbers-and-strings) section.

There's this thing in math called "base". It defines how we may represent
quantities in different formats. Us, humans, normally use the decimal
base (base 10) which allows any number to be represented with the digits
`0`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8` and `9`.

To make our next examples clearer I will call the number "20" as "decimal 20".

Binary numbers (base 2) can represent any number, but using only two
distinct digits: `0` and `1`.

The decimal 20 when represented in binary form, can be seen as 0b000**10100**.
Do not worry about converting it, let the machines do this job 😉

Hexadecimal numbers (base 16) can represent any number and, to do so, it uses
not only the ten digits `0`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8` and `9` but
also additional six characters borrowed from the latin alphabet: `a`, `b`, `c`,
`d`, `e` and `f`.

The same decimal 20 is represented in hexadecimal as the number `0x14`. Again,
don't try to convert this to decimal in your head, our computers are experts in it!

**What is important for you to understand is that numbers can be represented in
different bases:** binary (base 2), octal (base 8), decimal (base 10, our common
base) and hexadecimal (base 16).

In PHP and many other languages, **binary numbers** are written as any other numbers
but with a **prefix 0b**, like the decimal 20 represented as **0b**00010100. **Hexadecimal**
numbers receive a **prefix 0x**, like the decimal 20 represented as **0x**14.

As you might have heard already, computers don't store literal data. They
represent everything as binary numbers instead: 0s and 1s. Characters,
numbers, symbols, instructions... everything is represented using base 2.
Characters are just a convention of number sequences: the character 'a',
for example, is the number 97 in the ASCII table.

Even though everything is stored as binary, the most convenient way for
programmers to read this data is using hexadecimals. They just look good.
I mean, look at this!

```
# string "abc"
'abc'

# binary form (bleh)
0b01100001 0b01100010 0b01100011

# hexadecimal form (such wow)
0x61 0x62 0x63
```

While binary takes up lots of visual space, hexadecimals are very neat to
represent binary data. That's why we normally stick with them when doing
low-level programming.

## Carry operations { #carry-operations }

You're already familiar with the concept of Carry, but I need you to pay
attention to it so we can use it with different bases.

With the decimal set we have ten distinct digits to represent numbers from
zero (0) to nine (9). But whenever we try to represent numbers bigger than
9 we run out of digits! Thus a Carry operation happens: we prefix our number
with the digit one (1) and reset the right digit to zero (0).

```
# decimal (base 10)
1 + 1 = 2
2 + 2 = 4
9 + 1 = 10 // <- Carry
```

The binary base will have similar behaviour, but is limited to digits 0 and 1.

```
# binary (base 2)
0 + 0  = 0
0 + 1  = 1
1 + 1  = 10 // <- Carry
1 + 10 = 11
```

The same happens with hexadecimal base, but with a much wider range.

```
# hexadecimal (base 16)
1 + 9  = a // no carry, a is in range
1 + a  = b
1 + f  = 10 // <- Carry
1 + 10 = 11
```

As you realized, carry operations demand more digits to represent a certain number.
This allows you to understand how certain data types are limited and, as
they're stored in computers, their limitation is represented in binary form.

## Data representation in computer memory { #memory-representation }

As I mentioned before, computers store everything using binary format. So only 0s
and 1s are effectively stored.

The easiest way to visualize how they are stored, is by imagining a big table with
a single row and many columns (as many as storage capacity), where each column is
a binary digit (bit).

Representing our decimal 20 in such table using only 8 bits, looks like the following:

<table><tbody>
<tr>
  <th>Position (Address)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>0</td><td>0</td><td>0</td><td>1</td><td>0</td><td>1</td><td>0</td><td>0</td>
</tr>
</tbody></table>

An unsigned 8-bit Integer is a number that can only be represented with
at most 8 binary digits. So **0b11111111** (decimal 255) is the biggest number an
unsigned 8-bit integer can store. Adding 1 to it would require a Carry operation,
which cannot be represented with the same amount of digits.

With this in mind we can easily understand why there are so many memory
representations for numbers and what they effectively are: uint8 is an unsigned
8-bit Integer (decimal 0 to 255), uint16 is an unsigned 16-bit Integer (decimal
0 to 65,535). There are also uint32, uint64 and theoretically higher ones.

Signed integers, which can represent negative values too, normally use the very
last bit to determine whether a number is positive (last bit = 0) or negative
(last bit = 1). As you can imagine, they then are capable of storing smaller
values with the same amount of memory. A signed 8-bit integer will range from
decimal -128 until decimal 127.

Here's a decimal -20 represented as a signed 8-bit integer. Notice its first bit
(address 0) is set (equals to 1), this marks the number as negative.

<table><tbody>
<tr>
  <th>Position (Address)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>1</td><td>0</td><td>0</td><td>1</td><td>0</td><td>1</td><td>0</td><td>0</td>
</tr>
</tbody></table>

I hope everything is making sense so far. This introduction is very important for
you to understand how computers work internally. Only then you'll feel comfortable
with what PHP is actually doing under the hood, we'll have to always keep it in mind.

## Arithmetic Overflows { #arithmetic-overflows }

The way numbers are chosen to be represented (8-bits, 16-bits...) will determine
their minimum and maximum value range. And that's basically because of how they are
stored in memory: adding 1 to a binary digit 1 should result in a Carry operation,
meaning another bit is necessary to prefix the actual number.

Since integer formats are very well defined it is not possible to rely on Carry
operations that go above that limit. (IT IS actually possible, but a little insane)

<table><tbody>
<tr>
  <th>Position (Address)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>0</td>
</tr>
</tbody></table>

Here we are very close to the 8-bit limit (decimal 255). If we add one to it, we'll
end up with the decimal 255 and the following binary representation:

<table><tbody>
<tr>
  <th>Position (Address)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td>
</tr>
</tbody></table>

All bits are set! Adding 1 to this would require a Carry operation, which cannot happen
because we don't have enough bits: all 8 bits are set! This results in a thing called
**overflow**, which happens when you try to go above a certain limit. The binary operation
255 + 2 should result in 1 when you read its 8-bit result.

<table><tbody>
<tr>
  <th>Position (Address)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>1</td>
</tr>
</tbody></table>

This behaviour is not random, there's a calculation involved there to determine what's
the new value which is not relevant here.

## Binary numbers and strings in PHP { #binary-numbers-and-strings }

Ok, back to PHP! Sorry about the big detour, but I think it was necessary.

I hope by now dots are starting to connect in your head: binary numbers, how they are
stored, what an overflow is, how php represents numbers...

The decimal 20 represented in a PHP integer may have two different representations,
depending on your platform. The x86 platform represents it with 32 bits while the x64
does it with 64 bits, both of them are signed (allowing negatives). We all know that
decimal 20 can fit in a 8-bit space, but PHP treats every decimal number as a 32 or 64
bits number.

PHP also has binary strings which can be converted back and forth by using the
[pack()](https://www.php.net/manual/en/function.pack.php) and
[unpack()](https://www.php.net/manual/en/function.unpack.php) functions.

The main difference between binary strings and numbers in PHP is that binary strings
are just holding the data, like a buffer. While PHP integers (binary or not) let us
perform arithmetic operations on them such as sum and subtraction, and also binary
(bitwise) operations such as AND, OR, XOR and NOT.

## Binary: Integers or Strings, which to use in PHP? { #integers-or-strings }

To transport data we normally use binary strings. So reading a binary file or network
communication will require us to pack and unpack our binary strings.

Actual operations such as OR and XOR cannot reliably happen on strings, so we must use
them with integers.

## Debugging binary values in PHP { #debugging-binary-in-php }

Now comes the fun! Let's get our hands dirty and play a bit with some PHP code!

The first thing I will show you is to visualize the data. We need to understand what
we're dealing with afterall.

Debugging integers is actually very very simple, we can just use the
[sprintf()](https://www.php.net/manual/en/function.sprintf) function. Its formatting
is very powerful and will help us to quickly realize what those values are.

Below I will represent the decimal 20 in a 8-bit binary format and 1-byte hexadecimal
format.

```php
<?php
// Decimal 20
$n = 20;

echo sprintf('%08b', $n) . "\n";
echo sprintf('%02X', $n) . "\n";

// Output:
00010100
14
```

The format `%08b` makes the variable `$n` to be printed as a binary representation (b)
with 8 digits (08).

The format `%02X` represents the variable `$n` in hexadecimal (X) and 2 digits (02).

### Visualizing binary strings { #visualizing-binary-strings }

While PHP integers are always 32 or 64 bits long, strings are as long as their content.
To decode their binary values and visualize what's going on we need to inspect and
convert each byte.

Luckily PHP strings are dereferencable just as arrays are, and each position points to a
char with 1 byte size. Here's a quick example of how chars can be accessed:

```php
<?php
$str = 'thephp.website';

echo $str[3];
echo $str[4];
echo $str[5];

// Outputs:
php
```

Trusting that each char is 1 byte, we can easily call the
[ord()](https://www.php.net/manual/en/function.ord) function to cast it to a 1-byte
integer. Like this:

```php
<?php
$str = 'thephp.website';

$f = ord($str[3]);
$s = ord($str[4]);
$t = ord($str[5]);

echo sprintf(
  '%02X %02X %02X',
  $f,
  $s,
  $t,
);
// Outputs:
70 68 70
```

We can see we're in a good path by double checking with the command line application hexdump:

```shell
$ echo 'php' | hexdump
// Outputs
0000000 70 68 70 ...
```

Where the first column is the address only, from the second column on we see hexadecimal
values representing the chars `p`, `h` and `p`.

Additionally we may use the [pack()](https://www.php.net/manual/en/function.pack.php) and
[unpack()](https://www.php.net/manual/en/function.pack.php) functions when handling binary
strings and I have a great example for you right here!!

Let's say we want to read a JPEG file to fetch some of its data (like EXIF, for example).
We may open the file handle using the read binary mode. Let's do this and immediately read
the first 2 bytes:

```php
<?php

$h = fopen('file.jpeg', 'rb');

// Read 2 bytes
$soi = fread($h, 2);
```

In order to fetch these values into an integer array we can simply unpack them like this:

```php
$ints = unpack('C*', $soi);

var_dump($ints);
// Outputs
array(2) {
  [1] => int(-1)
  [2] => int(-40)
}

echo sprintf('%02X', $ints[1]);
echo sprintf('%02X', $ints[2]);
// Outputs
FFD8
```

Note that the format `C` in the unpack() function will decode a char in the string `$soi`
as unsigned 8-bit numbers. The star modified `*` makes it unpack the entire string.

## Bitwise Operations { #bitwise-operations }

PHP implements all bitwise operations one might need. They are built as expressions
and their results are described below:

<table>
<thead>
  <th>PHP Code</th><th>Name</th><th>Description</th>
</thead>
<tbody>
  <tr>
    <td>$x | $y</td><td>Inclusive Or</td><td>A value with all bits set in both $x and $y</td>
  </tr>
  <tr>
    <td>$x ^ $y</td><td>Exclusive Or</td><td>A value with bits set in $x or $y but never both</td>
  </tr>
  <tr>
    <td>$x & $y</td><td>And</td><td>A value with bits set in $x and $y at the same time only</td>
  </tr>
  <tr>
    <td>~$x</td><td>Not</td><td>Flips all bits in $x</td>
  </tr>
  <tr>
    <td>$x << $y</td><td>Left Shift</td><td>Shifts the bits of $x to the left $y times</td>
  </tr>
  <tr>
    <td>$x >> $y</td><td>Right Shift</td><td>Shifts the bits of $x to the right $y times</td>
  </tr>
</tbody>
</table>

I'll explain one by one how they work, do not worry!

Let's assume that `$x = 0x20` and `$y = 0x30`. The examples below will present them using binary
notation to make things clearer.

### How Inclusive Or (`$x | $y`) works

The inclusive Or operation will produce a result taking all bits set from both inputs. So the
operation `$x | $y` must return `0x30`. See what's going on below:

```
// 1 | 1 = 1
// 1 | 0 = 1
// 0 | 0 = 0

0b00100000 // $x = 0x20
0b00110000 // $y = 0x30
OR ------- // $x | $y
0b00110000 // 0x30
```

**Notice:** from right to left, the 6th bit of $x was set (equals to 1) while the 5th and 6th
bits of $y were also set. The result merges both and generates a value with bits 5 and 6
set: `0x30`.

### How Exclusive Or (`$x ^ $y`) works

The exclusive Or (also known as Xor) will only capture bits that exist in a single side.
So the result of `$x ^ $y` is `0x10`. See the example below:

```
// 1 ^ 1 = 0
// 1 ^ 0 = 1
// 0 ^ 0 = 0

0b00100000 // $x = 0x20
0b00110000 // $y = 0x30
XOR ------ // $x ^ $y
0b00010000 // 0x10
```

### how And (`$x & $y`) works

The AND operator is much simpler to understand. It performs the AND operation on each bit so
only values that match on both sides at the same time will be retrieved.

The result of `$x & $y` is `0x20`, I show you why:

```
// 1 & 1 = 1
// 1 & 0 = 0
// 0 & 0 = 0

0b00100000 // $x = 0x20
0b00110000 // $y = 0x30
AND ------ // $x & $y
0b00100000 // 0x20
```

### How Not (`~$x`) works

The NOT operation requires a single parameter and it simply flips all bits passed. It
transforms all bits with value 0 into 1, and all bits with value 1 into 0. See below:

```
// ~1 = 0
// ~0 = 1

0b00100000 // $x = 0x20
NOT ------ // ~$x
0b11011111 // 0xDF
```

If you ran this operation in PHP and decided to debug it using sprintf() you probably
noticed a much wider number, right? I'll explain to you what's going on and how to fix
it below in the [Normalizing integers](#normalizing-integers) section.

### How Left and Right shifts (`$x << $n` and `$x >> $n`) work

Shifting bits are the same as multiplying or dividing numbers by multiples of two. What
it does is to make all bits travel `$n` steps to the left or right.

I'll take a smaller binary number to represent this one, so things get easier to
comprehend. Take `$x = 0b0010` as an example. If we shift `$x` to the left once, that
bit 1 should move one step to the left:

```php
$x = 0b0010;
$x = $x << 1;
// 0b0100
```

The same happens with the right shift. Now that `$x = 0b0100` let's shift it to the
right twice:

```php
$x = 0b0100;
$x = $x >> 2;
// 0b0001
```

Effectively, shifting a number `$n` times to the left is the same as multiplying it by
two `$n` times and shifting a number `$n` times to the right is the same as dividing it
by two `$n` times.

## What is a bitmask { #what-is-bitmask }

There are many cool things we can do with these operations and other techniques. One great
technique to always remember is the bitmask.

A bitmask is just an arbitrary binary of your choice, crafted to extract a very specific
information.

For example, let's take the idea that an 8-bit signed integer is positive when the 8th
bit is not set (equals 0) and is negative when it is set. I then ask the question,
is `0x20` positive or negative? And what about `0x81`?

For this we can craft a very convenient byte with only the negative bit set (`0b10000000`,
equivalent to `0x80`) and use the `AND` operation against `0x20`. If the result is equal to
`0x80` (`0b10000000`, our mask) then it is a negative number, otherwise it is a positive number:

```
// 0x80 === 0b10000000 (bitmask)
// 0x20 === 0b00100000
// 0x81 === 0b10000001

0x20 & 0x80 === 0x80 // false
0x81 & 0x80 === 0x80 // true
```

This is often necessary when you're dealing with flags. You can even find usage examples
in PHP itself: the
[error reporting flags](https://www.php.net/manual/en/function.error-reporting.php).

It is possible to choose what kind of errors will be reported like this:

```php
error_reporting(E_WARNING | E_NOTICE);
```

What's going on there? Well, just check the value you provided:

```
0b00000010 (0x02) E_WARNING
0b00001000 (0x08) E_NOTICE
OR -------
0b00001010 (0x0A)
```

So whenever PHP sees a Notice that could be reported it will check something like this:

```
// error reporting we set before
$e_level = 0x0A;

// Needs to throw a notice
if ($e_level & E_NOTICE === E_NOTICE)
 // Flag is set: throws notice
```

And you will see this everywhere! Binary files, processors, all sorts of low level stuff!

## Normalizing integers { #normalizing-integers }

There's this very specific thing about PHP when handling binary numbers: our integers
are 32 or 64-bit wide. This means that often we will have to normalize them to be able
to trust our calculations.

For example, running the following operation in a 64-bit machine will get us an odd
(but expected) result:

```php
echo sprintf(
  '0b%08b',
  ~0x20
);

// Expected
0b11011111
// Actual
0b1111111111111111111111111111111111111111111111111111111111011111
```

What happened there?! Well, a `NOT` in that 8-bit integer (`0x20`) flipped all zero bits and
transformed them into 1s. Guess what used to be zero? Exactly, all other `56` bits to the left
that we ignored before!

Again, this is because PHP's integers are 32 or 64-bit long no matter which value you put inside!

This still works as you would expect, though. For example the operation
`~0x20 & 0b11011111 === 0b11011111` results in `bool(true)`. But always keep in mind that these
bits to the left are constantly there or you might end up having weird behaviours in your code.

To solve this issue, you can normalize your integers by applying a bitmask that clears all those
zeros. For example, to normalize `~0x20` into an 8-bit integer we must `AND` it with `0xFF`
(`0b11111111`) so all previous `56` bits will be set to zero.

```
~0x20 & 0xFF
-> 0b11011111
```

**Heads up!** Never forget what you're carrying in your variables otherwise you may end up with
an unexpected behavior. For example, let's see what happens when we right shift the above
value with and without 8-bit masking.

```
~0x20 & 0xFF
-> 0b11011111

0b11011111 >> 2
-> 0b00110111 // expected

(~0x20 & 0xFF) >> 2
-> 0b00110111 // expected

(~0x20 >> 2) & 0xFF
-> 0b11110111 // expected?
```

Just to make it clear: from the PHP stand point this IS expected, because you're clearly handling
a 64-bit integer there. You must make it clear what YOUR program expects.

**Pro tip:** avoid silly mistakes like these by [coding with TDD](/en/issue/real-life-tdd-php/).

## Conclusion: binary is cool and so is PHP { #conclusion-examples }

I hope you enjoyed your read as much as I enjoyed writing this blog post. Most importantly:
I hope this knowledge will enable you to take an adventure in this amazing world of binary data.

With these tools in hand, everything else is just a matter of finding the proper documentation
on how binary files/protocols behave. Everything is a binary sequence after all.

I highly recommend you to have a look at the PDF spec, or the EXIF for image metadata. You
may even want to play with your own implementation of the
[MessagePack serialization format](/en/issue/messagepack-vs-json-benchmark/) or maybe Avro,
Protobuf... Endless possibilities!

As you might have noticed, this article took me quite a bit (see what I did?) to write. If
you'd like to reward the effort, please be so nice to share it and bookmark if you need to
use it as reference.

Maybe soon I'll come back with some practical binary stuff :)

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
  "headline": "Mastering binary and bitwise in PHP",
  "description": "I recently caught myself working on different projects that required me to rely heavily on bitwise operations in PHP. From reading binary files to emulating processors, this is a very useful knowledge to have and a very cool one too.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/20-bitwise-php/cover-640.webp"
   ],
  "datePublished": "2021-01-07T00:00:00+08:00",
  "dateModified": "2021-01-07T00:00:00+08:00",
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
