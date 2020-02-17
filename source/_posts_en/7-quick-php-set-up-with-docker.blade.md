---
slug: php-docker-quick-setup
title: Setting up PHP, Docker and PHPUnit
createdAt: 2020-02-17
sitemap:
  lastModified: 2020-02-17
image:
  url: /assets/images/posts/7-container-640.webp
  alt: 'A man jumping over a container'
tags:
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

[Leia em Português](/br/edicao/php-docker-setup-rapido/)

Here I'll show you some gists on my basic
set up for bootstraping php applications.

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

You can find the final results in
[the public repository](https://github.com/nawarian/The-PHP-Website/tree/master/code/7-phpunit-docker-compose).

The main idea is that every service may or not become
a command. And the pattern would be the following:

```bash
$ docker-compose run <command> [--args]
```

Running a test suite, for example, could look like
this:

```bash
$ docker-compose run tests
```

To make typing easier, we can also add and alias to
the `docker-compose run` command. I'll call it `dcr` here:

```bash
$ alias dcr='docker-compose run'
$ dcr lol
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
$ mkdir -p src/ tests/ bin/ \
  .conf/nginx/ var/
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
      - COMPOSER_CACHE_DIR=/app/var/cache/composer
    volumes:
      - .:/app
    restart: never
```

The above snippet will create a `composer` service,
that maps the current path to `/app` inside the container.

Setting the environment variable COMPOSER_CACHE_DIR to
`/app/var/cache/composer` will make sure that composer will have
a local cache instead of downloading everything again all the time.

So make sure that you don't push to git your `var/` local folder,
huh!

Just so you don't forget, let's ignore composer related
files right away. Run the following commands to avoid
commiting composer files:

```bash
$ echo 'vendor/' >> .gitignore
$ echo 'var/' >> .gitignore
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

For setting up php fpm, we will need actually
two different services. One HTTP server and the
FPM instance itself.

As they are long-running processes, we won't use
the `docker-compose run` form with them. Instead,
let's lift both using the `up -d` version.

Final command will look like the following:

```bash
$ docker-compose up -d fpm nginx
```

Let's first add PHP-FPM to the game:

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
  phpunit:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
    entrypoint: vendor/bin/phpunit
  # NEW IN THIS SECTION!!!
  fpm:
    image: php:7.4-fpm
    restart: always
    volumes:
      - .:/app
    
```

Very simple! By running `docker-compose up -d fpm`
it should already start running in background.

Now let's set up the NGINX part that will
expose a port `8080` and handle php requests
by forwarding them to fpm's port `9000`.

The docker-compose.yml file should be like this:

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
  phpunit:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
    entrypoint: vendor/bin/phpunit
  fpm:
    image: php:7.4-fpm
    restart: always
    volumes:
      - .:/app
  # NEW IN THIS SECTION!!!
  nginx:
    image: nginx:1.17.8-alpine
    ports:
      - 8080:80
    volumes:
      - .:/app
      - ./var/log/nginx:/var/log/nginx
      - .conf/nginx/site.conf:/etc/nginx/conf.d/default.conf

```

With this we expose the port `8080` to
be the container's `80` (default http port).

We also linked our current directory to `/app`.
Normally people do `/var/www` but I'd like to keep
it consistent with our previous services.

The `var/log/nginx` local path got linked to
`/var/log/nginx`. This way we don't get blind when
in need to check access or error logs.

Last but not least, the `site.conf` file got introduced
to the container with the name `default.conf`. This is
just a quick way for nginx to pick it up.

We need to create our config file now. Let's do it!

```bash
$ touch .conf/nginx/site.conf
```

Write the following config to your local
`.conf/nginx/site.conf` file:

```conf
# .conf/nginx/site.conf
server {
  listen 80;
  listen [::]:80;

  root /app/public;
  index index.php;

  location / {
      try_files $uri $uri/ /index.php$is_args$args;
  }

  location ~ .php$ {
      try_files $uri =404;
      fastcgi_split_path_info ^(.+.php)(/.+)$;
      fastcgi_pass fpm:9000;
      fastcgi_index index.php;
      include fastcgi_params;
      fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
      fastcgi_param PATH_INFO $fastcgi_path_info;
  }

  access_log /var/log/nginx/myapp_access.log;
  error_log /var/log/nginx/myapp_error.log;
}
```

Notice that `root` is set to `/app/public`.
This will be the entry path to every request
nginx handles.

Also look at the `fastcgi_pass` and see that
it points to `fpm:9000`. **This is our fpm image.
If you named it differently, adjust this line
as well!**

To test this out, let's create a simple
`index.php` inside our `/public` folder.
This file will serve as our application's
entry point.

Just add a simple phpinfo call to this file:

```php
# public/index.php
<?php

phpinfo();

```

Now just lift the nginx server:

```bash
$ docker-compose up -d nginx
```

From this moment on you should be able to
enter http://localhost:8080/ from your
browser normally.

## Don't forget the autoloader

We installed composer properly, but
using our own classes is still not
optimal.

Let's adjust our composer.json file
so composer knows from where to
autoload stuff:

```json
# composer.json
{
  "require-dev": {
    "phpunit/phpunit": "^9.0"
  },
  "autoload": {
    "psr-4": {
      "ThePHPWebsite\\": "src/"
    }
  }
}

```

Now just run a composer dump:

```bash
$ dcr composer -- dump
Generated autoload files
containing 646 classes
```

This `--` before the actual command just
makes sure that `docker-compose` won't
think `dump` is a service instead of a
parameter to our command.

To test this, let's create a file named
`App.php` inside `src/`:

```php
# src/App.php
<?php

declare(strict_types=1);

namespace ThePHPWebsite;

class App
{
  public function sayHello(): void
  {
    echo 'Hello!';
  }
}
```

And now just modify the `public/index.php`
in order to use our new App class:

```php
<?php

require_once __DIR__
  . '/../vendor/autoload.php';

use ThePHPWebsite\App;

$app = new App();

$app->sayHello();

```

Refresh your browser tab and we shall
see a "Hello!" message on the screen!

---

Yeah, that's it! An awesome guide on setting
up a local development environment for
PHP very quickly using docker compose and enabled
to run tests with phpunit.

You might want to add some other things as well,
like databases, queues or maybe a Solr server...

You're free to evolve without messing up your whole
computer/server/repository.

If at some point you find that you'll need
specific things like a certain php extension or
something fancy about your containers, just
create your custom Docker and replace in your
docker-compose.yml file.

Don't forget to share with your lazy friends
whenever they start crying about a skeleton set up
for PHP.

And, of course, let me know if you faced any trouble
during this tutorial.

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
  "headline": "Setting up PHP, Docker and PHPUnit",
  "description": "In this post I quickly show my custom setup for php applications using PHPUnit and Docker and quick configs almost every application needs.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/7-container-640.webp"
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
