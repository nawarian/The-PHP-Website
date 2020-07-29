---
slug: most-used-php-functions
title: What are the top 20 most used php functions by frameworks?
category: thoughts
createdAt: 2020-06-16
sitemap:
  lastModified: 2020-06-27
image:
  url: '/assets/images/posts/15-question-mark-640.webp'
  alt: 'A big question mark centered on screen'
tags:
  - curiosity
  - core
  - report
  - top
meta:
  description:
    Out of curiosity I decided to rank php functions usage
    by popular frameworks.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/funcoes-mais-usadas-php/)

<s>I deeply question how useful this post is. But I think it
make sense to share it and also the code that helped me
achieving these numbers.</s> Thank you for your feedback,
turns out this list was much cooler than I thought and I'm very
happy how it was received.

This post is also a hearbeat, to show you I've been just busy
but didn't give up on this project 😉.

Below I show the top 20 most used functions by php frameworks.

You can find the code I used to fetch it in this
[github repository](https://github.com/nawarian/The-PHP-Website/tree/master/code/15-framework-functions/).

<s>I've collected this data based on their main branches
today's state. (2020-06-16)</s>

**Edit (2020-06-27):** as requested, Yii2 framework has been added
and the entire list updated. So all code here relates to their main
branches to this date. Alongise, I've decided to also add PHPUnit and
WordPress since many of you expressed curiosity on them!

**Edit (2020-07-23):** magento2 also was requested to join this list,
so here it is! Really mind blowing results IMO. It kicked out `strpos()`
from the top five to bring `implode()` in its place ^^

Feel free to [ping me on twitter](https://twitter.com/nawarian)
if you'd like to add another framework here! 😊

## Feature request: Top of of the pops!

Since many of you requested, here goes a list of the top 5
functions from the whole list.

I'll skip `sprintf()` because symfony alone pushes
this up to `2_746` and it is kind of unfair.

So the compiled list follows:

| Function | Usages |
| ------- | ------ |
is_array | 3943
substr | 3784
count | 2948
in_array | 2729
implode | 2334

I must say I'm very impressed about how often we use `is_array()`
and `count()`. I tested in some repositories I work with and in
some the number of calls to `count()` is around 3k.

Cool stuff 🤣

## Top 20 php functions used by WordPress

| Function | Usages |
| ------- | ------ |
substr | 2200
sprintf | 1939
in_array | 912
is_array | 769
strpos | 667
printf | 633
define | 584
str_replace | 572
trim | 566
count | 556
strlen | 541
preg_match | 501
defined | 432
function_exists | 430
implode | 417
preg_replace | 412
explode | 385
array_merge | 372
array_keys | 275
is_string | 275

## Top 20 php functions used by Magento 2

| Function | Usages |
| ------- | ------ |
is_array | 1420 |
sprintf | 1141
count | 1045
array_merge | 976
in_array | 901
implode | 822
array_keys | 697
explode | 674
trim | 491
str_replace | 475
array_key_exists | 441
substr | 367
strlen | 358
strpos | 358
is_string | 330
preg_match | 296
strtolower | 293
json_encode | 269
get_class | 267
is_numeric | 262

## Top 20 php functions used by PHPUnit

| Function | Usages |
| ------- | ------ |
sprintf | 188
func_get_args | 183
count | 90
strpos | 49
explode | 43
trim | 39
class_exists | 39
get_class | 39
assert | 38
file_get_contents | 34
implode | 32
preg_match | 32
substr | 32
is_string | 31
is_array | 29
array_merge | 26
strlen | 26
in_array | 23
str_replace | 21
is_object | 17

## Top 20 php functions used by Symfony

| Function | Usages |
| ------- | ------ |
sprintf | 2746
substr | 715
strpos | 602
count | 590
is_array | 573
strlen | 446
implode | 438
class_exists | 415
is_string | 338
preg_match | 338
in_array | 336
str_replace | 310
array_merge | 271
array_keys | 235
get_class | 226
array_key_exists | 193
explode | 192
is_object | 164
preg_replace | 157
strtolower | 154

Quite interesting to notice that `sprintf` is the most used one.
Probably because of [Symfony's coding standards](https://symfony.com/doc/current/contributing/code/standards.html)
towards throwing exceptions.

> Exception and error message strings must be concatenated using sprintf;

## Top 20 functions used by Yii2

| Function | Usages |
| ------- | ------ |
is_array | 300
implode | 210
strpos | 182
substr | 172
array_merge | 150
count | 147
is_string | 140
trim | 118
preg_match | 107
str_replace | 104
get_class | 104
call_user_func | 103
in_array | 91
array_keys | 90
strncmp | 77
explode | 73
preg_replace | 66
strlen | 62
array_key_exists | 62
reset | 56

## Top 20 php functions used by Laravel

| Function | Usages |
| ------- | ------ |
is_null | 450
is_array | 250
array_merge | 203
func_get_args | 155
count | 149
str_replace | 148
is_string | 132
in_array | 126
explode | 120
trim | 110
method_exists | 102
implode | 93
get_class | 86
function_exists | 79
compact | 75
array_map | 72
is_numeric | 71
sprintf | 68
call_user_func | 63
array_values | 61

## Top 20 php functions used by Amp

| Function | Usages |
| ------- | ------ |
assert | 27
range | 26
microtime | 17
array_shift | 14
debug_backtrace | 11
get_class | 11
is_int | 11
call_user_func_array | 11
sprintf | 10
is_resource | 9
posix_kill | 9
getmypid | 9
count | 8
usleep | 6
fwrite | 6
printf | 6
stream_socket_pair | 5
gc_collect_cycles | 5
defined | 5
substr | 5

I think is kind of unfair to run this program against Amp or Laminas
because they are composed by many different packages at once. So the
root repository isn't really fetching all dependencies.

I still find quite cool the difference in nature of function calls
here in comparison with other frameworks ^^.

## Top 20 php functions used by Cake PHP

| Function | Usages |
| ------- | ------ |
sprintf | 480
is_array | 367
strpos | 235
implode | 233
count | 216
is_string | 206
in_array | 198
substr | 172
explode | 159
array_merge | 139
str_replace | 133
preg_match | 108
strtolower | 98
array_keys | 97
strlen | 95
array_filter | 91
is_numeric | 80
array_map | 71
is_int | 68
array_key_exists | 67

## Top 20 php functions used by Code Igniter 4

| Function | Usages |
| ------- | ------ |
is_array | 232
function_exists | 182
strpos | 172
str_replace | 151
count | 147
in_array | 141
is_null | 127
trim | 125
strlen | 110
is_string | 105
explode | 105
strtolower | 104
preg_match | 97
array_key_exists | 91
substr | 89
implode | 86
rtrim | 64
preg_replace | 64
defined | 60
define | 57

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
  "headline": "What are the top 20 most used php functions by frameworks",
  "description": "Out of curiosity I decided to rank php functions usage by popular frameworks.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/15-question-mark-640.webp"
   ],
  "datePublished": "2020-06-16T00:00:00+08:00",
  "dateModified": "2020-06-27T00:00:00+08:00",
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
