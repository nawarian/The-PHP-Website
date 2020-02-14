---
slug: php-docker-tdd-setup
title: Setting up PHP, Docker and PHPUnit
createdAt: 2020-02-14
sitemap:
  lastModified: 2020-02-14
image:
  url: /assets/images/posts/6-tdl-framework-640.webp
  alt: ''
tags:
  - tests
  - learning
  - phpunit
  - docker
meta:
  description:
    In this post I quickly show my custom setup
    for php applications using PHPUnit and Docker and
    quick configs almost every application needs.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/tdl-aprendizado-guiado-por-testes/)

Here I'll show you some gists on my basic
set up for bootstraping php applications.

As you probably saw from [my previous post about TDD](/en/issue/real-life-tdd-php),
I care a lot about testing and I hope this
set up will also support you with removing
possible barriers you might have for testing too.

**My biggest goal here, is that you'll bookmark
this post** so you can come back, copy and paste
stuff from here and start your new applications
whenever you need specific bits with low effort. 😉

The good thing about following this approach
is that you can easily switch between image versions
without bootstraping thousands of things at once.

So...

**Before we start:** Make sure you have `docker` and
`docker-compose` installed.

## The final result

If you follow this tutorial through, you'll be able
to execute different services by using `docker-compose`
commands.

The main idea is that every service may or not become
a command. And the pattern would be the following:

```bash
$ docker-compose run <command> [--args]
```

Running a test suite, for example, will look like
this:

```bash
$ docker-compose run tests
```

To make typing easier, we can also add and alias to
the `docker-compose run` command. I'll call it `dcr` here:

```bash
$ alias dcr='docker-compose run'
$ dcr run lol
ERROR: Can't find a suitable
configuration file in this
directory or any parent.
Are you in the right directory?

Supported filenames:
docker-compose.yml,
docker-compose.yaml
```

Alias created! It will complain though because there's no
compose file there yet. Let's create it then!

## A basic compose file

So we're creating a brand-new project, huh? Let's
do it! Start by **creating the project folder** and
later on **creating our docker-compose.yml** file:

```bash
$ mkdir my-project
$ cd my-project
$ touch docker-compose.yml
```

I'll create then the common folders my skeleton
usually has. This will include a source folder,
a folder for tests and a folder for binaries.

Just run this:

```bash
$ mkdir src/ tests/ bin/
```

Now we can start working with our `docker-compose.yml`
file. It will contain all dependencies this project
might have.

The initial content in our docker-compose file should
be quite simple. Just type in the following:

```yaml
# docker-compose.yml
version: '3'
services:
```

We will fill in the services right now! The most basic
one we need is, of course, composer.

## Adding composer to docker-compose

Probably we're going to use php from inside the container.
So **it doesn't make much sense to run composer from the
local machine**, as php version might differ.

Let's then add a `composer` service to our file:

```yaml
# docker-compose.yml
version: '3'
services:
  composer:
    image: composer:1.9.3
    environment:
      - COMPOSER_CACHE_DIR=/app/.cache/composer
    volumes:
      - .:/app
    restart: never
```

The above snippet will create a `composer` service,
that maps the current path to `/app` inside the container.

Setting the environment variable COMPOSER_CACHE_DIR to
`/app/.cache/composer` will make sure that composer will have
a local cache instead of downloading everything again all the time.

So make sure that you don't push to git your `.cache` local folder,
huh!

Just so you don't forget, let's ignore composer related
files right away. Run the following commands to avoid
commiting composer files:

```bash
$ echo '.cache/' >> .gitignore
$ echo 'vendor/' >> .gitignore
```

Great! With composer in hands we are already prepared
to install our most important dependency!

## Prepare PHPUnit

The most important dependency from this skeleton
app is the test engine, of course!

Let's install it by request it from composer:

```bash
$ dcr composer require --dev \
  phpunit/phpunit
```

You don't need this backslash by the way, I'll leave
it there so mobile readers can also benefit from this
text 😬

Should be installing deps right now, and a `composer.json`
and `composer.lock` files might have appeared in your
directory. Oh, there's a `vendor/`.

Things seem to work...

Let's then create a simple php service for handling
cli stuff. We will use the official php cli image for
such. And as fancy as we can get, let's do it with
php 7.4! 🔥

## Prepare a PHP Cli

We're gonna use the `php:7.4-cli` image for this.

Let's also map volumes the same way we did with
composer. Might be handy in the future.

```yaml
# docker-compose.yml
version: '3'
services:
  composer:
    image: composer:1.9.3
    environment:
      - COMPOSER_CACHE_DIR=/app/.cache/composer
    volumes:
      - .:/app
    restart: never
  # NEW IN THIS SECTION!!!
  php:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
```

Here we also set the working dir to `/app`.
So whenever we run `dcr php` it will act as
if it was in our local root path.

Wondering how we're gonna run unit tests,
right?

Lemme show ya!

## Run PHPUnit inside container

Running PHPUnit should be as simple as running
a cli command. Given it is a cli command...

The following then works fine:

```bash
$ dcr php vendor/bin/phpunit
```

You can use <TAB\> for auto-completion normally 😉

Sounds really boring to type all this stuff over
and over again, though. Can we make it simpler?

Yes!

Let's add a `phpunit` service to our `docker-compose.yml`:

```yaml
# docker-compose.yml
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
  # NEW IN THIS SECTION!!!
  phpunit:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
    entrypoint: vendor/bin/phpunit
```

The `entrypoint` field here is the catch!
Now in your terminal just run the following:

```
$ dcr phpunit --version
PHPUnit 9.0.1 by Sebastian
Bergmann and contributors.
```

Ohaa! That's beautiful!

We can, by the way, generate our `phpunit.xml`
configuration before moving to the next step.

Let's do it:

```bash
$ dcr phpunit \
  --generate-configuration
```

It will ask you a couple of questions. Just press
enter for everything, who cares...

## Create a simple test

Just to make sure things are working, right?

Let's do it!

```bash
$ touch tests/MyTest.php
```

And inside `tests/MyTest.php` add the following:

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
  public function testMyTest(): void
  {
    self::assertTrue(false);
  }
}
```

This works perfectly! And the test also fails...
You can fix it later, no worries!

Now that we managed to run our tests we can think
of building the application.

Probably you want to build a web application, right?
Let's then create something with nginx and PHP-FPM!!

## Web Server Set Up

- Add FPM
- Add nginx
- Add nginx configs
- Add a phpinfo.php file

## Add MariaDB or MySQL

- Add mariadb image
- Set credentials via env vars
- Show simple a connection with PDO

## Or maybe you'd like to use MongoDB

- Add a mongodb driver dependency
- Add a mongodb image
- Set credentials via env vars
- Show a simple connection with the driver

## In-Memory Store!

- Add redis image
- Connect with redis

## Tweak autoloader

- Modify `composer.json` file
- Play around with the web app
  - Test first!
  - Slim + Redis
- Play around with the cli app
  - Test first!
  - Raw app + Redis

---

Yeah, that's it! An awesome guide on setting
up a local development environment for
PHP very quickly using docker compose and enabled
to run tests with phpunit.

You might want to add some other things as well,
like the phpunit watcher, behat or maybe Rector.

You're free to evolve without messing up your whole
computer/server/repository.

Don't forget to share with your lazy friends
whenever they start crying about a skeleton set up
for PHP.

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
  "headline": "Setting up PHP, Docker and PHPUnit",
  "description": "In this post I quickly show my custom setup for php applications using PHPUnit and Docker and quick configs almost every application needs.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/6-tdl-framework-640.webp"
   ],
  "datePublished": "2020-02-01T00:00:00+08:00",
  "dateModified": "2020-02-01T00:00:00+08:00",
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
