---
slug: most-used-php-functions
title: What are the top 20 most used php functions by frameworks?
category: thoughts
createdAt: 2020-06-16
sitemap:
  lastModified: 2020-06-16
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

I deeply question how useful this post is. But I think it
make sense to share it and also the code that helped me
achieving these numbers.

This post is also a hearbeat, to show you I've been just busy
but didn't give up on this project 😉.

Below I show the top 20 most used functions by php frameworks.

You can find the code I used to fetch it in this
[github repository](https://github.com/nawarian/The-PHP-Website/tree/master/code/15-framework-functions/).

I've collected this data based on their main branches
today's state. (2020-06-16)

Feel free to [ping me on twitter](https://twitter.com/nawarian)
if you'd like to add another framework here! 😊

## Top 20 php functions used by Symfony

Function | Usages
-------- | ------
sprintf | 2743
substr | 708
strpos | 594
count | 588
is_array | 572
strlen | 445
implode | 436
class_exists | 415
is_string | 338
preg_match | 336
in_array | 333
str_replace | 310
array_merge | 271
array_keys | 234
get_class | 224
array_key_exists | 193
explode | 189
is_object | 164
preg_replace | 157
strtolower | 154

Quite interesting to notice that `sprintf` is the most used one.
Probably because of [Symfony's coding standards](https://symfony.com/doc/current/contributing/code/standards.html)
towards throwing exceptions.

> Exception and error message strings must be concatenated using sprintf;

## Top 20 php functions used by Laravel

Function | Usages
-------- | ------
is_null | 440
is_array | 243
array_merge | 196
func_get_args | 155
str_replace | 146
count | 143
is_string | 129
in_array | 120
explode | 119
trim | 111
method_exists | 97
implode | 91
get_class | 84
function_exists | 81
compact | 75
array_map | 72
is_numeric | 69
sprintf | 68
call_user_func | 61
array_values | 58

## Top 20 php functions used by Amp

Function | Usages
-------- | ------
is_int | 11
assert | 27
range | 26
microtime | 17
array_shift | 14
get_class | 11
debug_backtrace | 11
call_user_func_array | 11
sprintf | 10
getmypid | 9
posix_kill | 9
is_resource | 9
printf | 6
fwrite | 6
usleep | 6
count | 8
stream_socket_pair | 5
gc_collect_cycles | 5
substr | 5
defined | 5

I think is kind of unfair to run this program against Amp or Laminas
because they are composed by many different packages at once. So the
root repository isn't really fetching all dependencies.

I still find quite cool the difference in nature of function calls
here in comparison with other frameworks ^^.

## Top 20 php functions used by Cake PHP

Function | Usages
-------- | ------
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

Function | Usages
-------- | ------
is_array | 232
function_exists | 182
strpos | 172
str_replace | 151
count | 147
in_array | 141
is_null | 127
trim | 125
strlen | 110
explode | 105
is_string | 105
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
  "dateModified": "2020-04-16T00:00:00+08:00",
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
