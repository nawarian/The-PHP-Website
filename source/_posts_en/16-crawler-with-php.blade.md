---
slug: how-to-write-crawlers-with-php
title: How to write decent crawlers with php
category: guides
createdAt: 2020-07-20
sitemap:
  lastModified: 2020-07-20
image:
  url: /assets/images/posts/16-many-books-and-magazines-640.webp
  alt: 'Many books and magazines.'
tags:
  - crawlers
  - guide
meta:
  description:
    After this article you'll realize how much you were suffering
    with your PHP crawlers. There IS a better way. Let me show it
    to you 😉
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/como-escrever-crawlers-em-php/)

You probably have seen many posts on how to write crawlers with php. What differs
this post from others? I’ll make sure you don’t lose your mind thinking about
regular expressions, global variables and all sort of annoying stuff.

We’ll be using an amazing tool named Spatie/Crawler which will provide us with a
great interface for writing crawlers without going absolutely crazy!

**Below you’ll find a video of mine coding this crawler. Just scroll down if you’d
like to jump right into action!** 😉

## Our use case

This crawler will be rather simple and intends to fetch names, nicknames and e-mails
from the official PHP directory of people who support(ed) the language somehow.

You can check out the entire directory in this url: [https://people.php.net](https://people.php.net).

## Set up the environment

The environment set up will be very quick and dirty, I’ll just copy over the _composer_
and _php_ sections from this other post I wrote on
[how to quickly set up a docker environment for development](/en/issue/php-docker-quick-setup/).

My _docker-compose.yml_ file looks like this:

```yaml
version: '3'
services:
  composer:
    image: composer:1.9.3
    environment:
      - COMPOSER_CACHE_DIR=/app/.cache/composer
    volumes:
      - .:/app
    restart: never

  php:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
```

Now let’s require the packages:

```bash
$ docker-compose run \
  composer require \
    spatie/crawler \
    symfony/css-selector
```

All we need now is an entry point, let’s create a file bin/crawler.php:

```bash
$ mkdir bin
$ touch bin/crawler.php
```

Nice and simple, now just add the autoload requirement and we’re ready to start:

```php
// bin/crawler.php
<?php

require_once __DIR__ . 
  '/../vendor/autoload.php';
```

From now on, we can just run our program by typing docker-compose run php php
bin/crawler.php. Simple as that! 😉

```bash
$ docker-compose run php \
  php bin/crawler.php
```

## Let’s take a look on the target website

Normally we should navigate around and figure out how the website works: url patterns,
ajax calls, csrf tokens, if feeds or APIs are available.

In this case, none are available. We need a raw crawling to fetch html data and parse it.

I see some url patterns:
- Person profile page: people.php.net/{nickname}
- Directory page: people.php.net/?page={number}
- External links

Seems nice and simple! We should only care about parsing the HTML inside person
profile pages and ignore the other ones.

By checking the profile page we can quickly fetch the selectors that are important to us:
- Name: `h1[property=foaf:name]`
- Nickname: `h1[property=foaf:nick]`

We can also trust that people’s e-mail there are basically “{nick}@php.net”.
With this info in hands, let’s code!

## Crawling all php people and extracting public data

Below you'll find the code, but if you like videos more, just check this one
below:

<iframe style="margin: auto;" width="560" height="315" src="https://www.youtube.com/embed/HaMoYhTV1hI?start=21" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Unfortunately it is in Brazilian Portuguese, but I'll try to make an english version
soon. You can just follow the code, though. Is fairly simple, code is in english
and portuguese is a super cool language! :P

## Coding time!

So, `spatie/crawler` brings two very important abstract classes - which I wish were interfaces instead.

One of them is the `CrawlObserver` class, where we can hook into crawling
steps and manipulate http responses. Our logic enters here.

I'll write an observer very quickly with an anonymous class below:

```php
$observer = new class
  extends CrawlObserver
{
  public function crawled(
    $url,
    $response,
    $foundOnurl
  ) {
    $domCrawler = new DomCrawler(
      (string) $response->getBody()
    );
    
    $name = $domCrawler
      ->filter('h1[property="foaf:name"]')
      ->first()
      ->text();
    $nick = $domCrawler
      ->filter('h2[property="foaf:nick"]')
      ->first()
      ->text();
    $email = "{$nick}@php.net";
    
    echo "[{$email}] {$name} - {$nick}" . PHP_EOL;
  }
};
```

The above logic will fetch the properties we expect from our pages.
Of course we should also check whether we are in the correct page or not.

Now, the next important bit is the `CrawlProfile` abstract class. With this one
we can decide whether an URL should or not be accessed by an Observer.
Let's create one with an anonymous class here:

```php
$profile = new class
  extends CrawlProfile
{
  public function shouldCrawl(
    $url
  ): bool {
    return $url->getHost() ===
      'people.php.net';
  }
};
```

Above we're defining that we only want to follow internal links. That's because
this website links to many other repositories. And we don't want to crawl the 
entire php universe, do we?

With those two instances in hand, we can prepare the crawler itself and start it:

```php
Crawler::create()
  ->setCrawlObserver($observer)
  ->setCrawlProfile($profile)
  ->setDelayBetweenRequests(500)
  ->startCrawling(
    'https://people.php.net/'
  );
```

**Important!** Do you see that `setDelayBetweenRequests(500)`? It makes the crawler
fetch one URL every 500 milliseconds. That's because we don't want to take this website
down, right? (Really, please don't. Pick a governmental website from Brazil instead 👀)

## That's it

Quick and dirty, but most importantly: sane! `spatie/crawler` provides a great interface
that simplifies the process a lot.

Mix this tool with proper dependency injection and queueing and you'll have professional
results.

Ping me on twitter if you have any questions!
Cheers! 👋

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
  "headline": "How to write decent crawlers with php",
  "description": "After this article you'll realize how much you were suffering with your PHP crawlers. There IS a better way.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/16-many-books-and-magazines-640.webp"
   ],
  "datePublished": "2020-07-20T00:00:00+08:00",
  "dateModified": "2020-07-20T00:00:00+08:00",
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
