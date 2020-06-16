---
slug: feature-flags-flipt-php
title: Using Feature Flags in PHP with Flipt and gRPC
category: guides
createdAt: 2020-06-08
sitemap:
  lastModified: 2020-06-08
image:
  url: /assets/images/posts/14-snake-640.webp
  alt: ''
tags:
  - deployment
  - security
meta:
  description:
    You're probably already familiar with the Tag & Deployment way
    of releasing code. In this post I'll show you a much safer manner
    to release code in a reliable way with Feature Flags.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/feature-flags-flipt-php/)

There are a couple of feature release management tools many of us
already dealt with. The most natural of them seems to be the
regular Release Tag and Deployment.

This Release Tag and Deployment methodology will eventually bring
trouble to your software in two possible ways:

1. In case your team has QA people, you'll end up holding back
releases until you have a set of changes big enough to justify
putting your QA to work on and have it released. Your software
delivery will be delayed.
1. In case you don't have QA people working with you, your
software will end up exploding in minor and patch versions,
costing you redeployments and constant uncertainty over the
software you just shipped.

**And this will occur with or without automated tests**, because
even though automated tests are great they simply cannot reproduce
all variables your software can run into.

Another releasing methodology not so widespread - at least for
my taste - is the usage of Feature Flags. Among all benefits I
see from feature flags, the biggest one is that they can
live with any other release methodology you might want to use.

## What exactly is a Feature Flag or Feature Toggle?

Feature Flags and Feature Toggles are the same: **a mechanism
for enabling/disabling features in your software during
runtime instead of by configuration or deployments.**

There's a very good read to better understand the
[concept of Feature Flags on this blog post from Martin Fowler](#).
**I'll try to make a TL;DR on it:**

- Feature Flags can be treated as booleans
- They are evaluated on runtime and can be changed during runtime
- You can use them for a/b testing or gradual releases
- They can grow your software's complexity over time

Often feature flags are used similar to the snippet below,
where a feature name is given (for example "enable hotkeys")
and a check is made to evaluate whether this flag is set
or not. If yes, a new implementation is added and, if not,
nothing happens and the code keeps executing as it was before.

```php
if (
  $featureFlag->isActive(
    'enable-hotkeys'
  )
) {
  // feature implementation...
}
```

## Are there feature flag frameworks or services available for PHP?

Yes! Most of the tools I've seen are paid, which for a company is
normally fine! But all paid ones I've seen so far are closed
source and require you to bind to their services and specifically
these two things I don't quite like.

The biggest reason is privacy and QoS: I really dislike tools
forcing my software to send strange telemetry and to be dependent
on their availability. The Feature Flag service fails, my
application fails (gracefully in most cases, to be fair). Also the
response times and caching are extremely important for such kind
of control over my software delivery and I can't stand the idea of
hitting another server to resolve a simple branching decision.

I know that normally such services will load all feature flags
during your software start up and cache them and their rules, so
you'll have to hit their servers as little as necessary. But this
still bugs me a lot.

One of the feature flag solutions that stood out to me lately is
the software [Flipt](https://flipt.io/)
which offers feature flags as a service that you can consume over
HTTP with its REST API or over gRPC.

Flipt is written in Go, if you believe that's even relevant and I
hope you don't 😬

Now, this is also a service. How can this be different from
third-party solutions then? Well, this service will run internally.
Alongside all your other services and applications. Depending on
how you configure its usage, you can decrease a lot the latency and
manage better its availability.

And no strange telemetry as well. Flipt is open, free and libre! 💚

<hr />

## A simple example: adding a cache layer to a Repository Class

Now, this is pretty typical, right? There's a query that normally
takes a few dozens of milliseconds and you are 100% sure that by
adding a cache layer you might get a decent performance boost there.

At the same time, this query is critical to your business because
it brings some history and if this query fails your client's
operation would be completely blind about their orders and would
be forced to stop. Your current implementation looks something like
this:

```php
// OrderHistoryRepository.php

interface OrderHistoryRepository
{
  public function fetchHistory(
    OrderId $order
  ): OrderHistory;
}
```

And the only implementation you have is the one touching a PgSQL
database:

```php
// OrderHistoryPgSQLRepository.php

class OrderHistoryPgSQLRepository
implements OrderHistoryRepository
{
  private PDO $client;

  public function fetchHistory(
    OrderId $order
  ): OrderHistory {
    $query = '...';
    $stmt = $this->client
      ->prepare($query);
    $stmt->execute([$orderId]);

    // Hydration code ...
  }
}
```

So you decide to add an in-memory cache layer that will attempt to
fetch you entry from a key, if existent it simply returns the cached
value and otherwise it fetches from PgSQL and stores in cache. You've
also decided that a TTL of half a day (12h) should be sufficient and
will give your database some peace:

```php
// OrderHistoryCachedRepository.php

class OrderHistoryCachedRepository
implements OrderHistoryRepository
{
  private OrderHistoryPgSQLRepository
    $pgSQLRepository;

  private CacheClient
    $cache;

  public function fetchHistory(
    OrderId $order
  ): OrderHistory {
    $cacheKey = '...';

    return $this->cache
      ->fetchAndStoreIfUnknown(
        $cacheKey,
        function () {
          return $this->pgSQLRepository
                   ->fetchHistory($order);
        },
        '12h' // ttl
      );
  }
}
```

Looks great! You update your dependency injection container
configuration to inject the cached repository instead of the pgsql
one and boom! You deploy, watch your dashboards and observe DB times
and requests per second are going down, QA is testing the feature
and things are passing, all automated tests passed normally. Pat
yourself on the back, you've done a great job, right?

Fifteen minutes later…

A new bug ticket emerges, and it says it is **critical**. Last time
you saw a critical bug was long ago and you can still remember the
chaos, the walls burning, people running desperately from one door
to another announcing it was the doomsday.

You immediately start checking all your monitoring dashboards, but
everything seems to be so… good? 🤔… Databases aren't under big
stress, requests aren't creating big load, the server is quieter
than you'd expect for such criticality level of a reported bug. You
then decide to finally open the bug ticket and read through.

One sentence, highlighted, red-colored, all capital case and
terribly big:
<p style="color: red!important;">“WE CAN'T FETCH ANY ORDER HISTORY,
OPS IS IDLE!!”</p>

Oof! That scary cold shiver is here again. You know this relates to
your changes!

So you start doing what any sane engineer would do: you revert your
commits, tag and deploy again.

What you didn't expect, though, is that the devops team decided to
upgrade the build system short after your last deployment. And for
some reason you don't understand, you can't deploy anymore.

Time is passing, money is being thrown out of the window. But don't
worry, at least the operations team is having fun, drinking a tea and
talking to each other about their weekends. They'll probably thank
you at some point, before their boss goes crazy about how far they
are to achieve their KPIs this week.

<figure style="text-align: center">
  <img src="/assets/images/posts/15-feature-flags/this-is-fine.webp" alt="This is fine dog." />
</figure>

Aaah that smell of chaos! 😌

## Let's tweak this example to become safer with Flipt and Feature Flags

Let's add the service Flipt to our set up, install php dependencies
and get it up and running with gRPC.

I'll assume you're using `docker-compose`. If you're not, take a
quick look my other post where
[I wrote how to quickly get up and running with no stress](/en/issue/php-docker-quick-setup/).

### Adding flipt to our docker-compose

Just add another service to your `docker-compose.yml` and call it
`flipt`. We're gonna use the author's docker image so we don't have
to bother compiling anything.

```yaml
services:
  ...
  flipt:
    image: markphelps/flipt
    ports:
      - 9000:9000 # gRPC port
      - 8081:8080 # UI port
```

### Adding gRPC to PHP

Flipt works with both gRPC and REST. Even though REST could be
implemented slightly faster it adds an overhead I'd rather avoid
here. So my choice for this tutorial is gRPC, which will even make
its usage familiar on different languages as well.

**gRPC is a remote procedure call protocol designed for high performance**.
Using it instead of HTTP implies we're not exchanging headers
without necessity.

[The gRPC client for PHP is available as an extension](https://github.com/grpc/grpc/tree/master/src/php)
which can be easily installed via `pecl`. So we will need to build
a custom docker image with the following contents:

```dockerfile
# .docker/php/fpm/Dockerfile

FROM php:7.4-fpm

# ...

RUN apt-get update \
    && apt-get install -y \
        libz-dev \
    && pecl install grpc-1.25.0 \
    && docker-php-ext-enable grpc
```

This builds an image with php fpm, install the `libz-dev`
packages and the `grpc` extension via pecl.

Then we just need modify our `docker-compose` file with the
following:

```yaml
fpm:
  # remove image like below
  # image: php:7.4-fpm
  build: .docker/php/fpm # NEW
  restart: always
  volumes:
    - .:/app
```

Then we can add the composer package.

```json
{
  ...
  "require": {
    ...,
    "grpc/grpc": "1.25.0"
  }
}
```

Now, one thing that is important to notice about flipt with gRPC
is that it is based on Protobuf. And the way Protobuf works is by
having its service definition file which can be compiled to
language-specific code.

We'll then reference the flipt's protobuf definition
[flipt.proto](https://github.com/markphelps/flipt/blob/master/rpc/flipt.proto).
But for this the `protoc` binary is also required. Let's bash this
into our PHP `Dockerfile` file so we solve this quickly:

```dockerfile
FROM php:7.4-fpm

RUN apt-get update \
    && apt-get install -y \
      libz-dev \
    && pecl install grpc-1.25.0 \
      protobuf-3.8.0 # NEW \
    && docker-php-ext-enable grpc \
      protobuf # NEW
``` 

Make sure you php cli will also have such extensions.

**Notice how I picked grpc v1.25.0 and protobuf v3.8.0!** That's not
a coincidence. gRPC has a [compatibility table with Protobuf](https://github.com/grpc/grpc/tree/master/src/php#protocol-buffers)
you must pay attention to.

With this we will finally be able to generate our php client to flipt.

Let's execute the following:

```bash
# Download flipt.proto file
$ curl https://raw.githubusercontent.com/markphelps/flipt/master/rpc/flipt.proto \
  --output var/flipt.proto -s

# Create alias to docker-compose run
$ alias dcr='docker-compose run'

# Compile to php classes
$ dcr protoc -I=. \
  ./var/flipt.proto \
  --php_out=./src/generated/flipt
```

### Updating our faulty code

What I would have done with feature flags in hand before I deployed
anything, would be to add the cache layer normally but simply bypass
its logic if the feature flag wasn't set.

It would then stay like this:

```php
// OrderHistoryCachedRepository.php

class OrderHistoryCachedRepository
implements OrderHistoryRepository
{
  private OrderHistoryPgSQLRepository
    $pgSQLRepository;

  private CacheClient
    $cache;

  private FeatureFlag
    $featureFlag;

  public function fetchHistory(
    OrderId $order
  ): OrderHistory {
    // By default it is FALSE,
    // so we fallback to the PgSQL
    // Repository right away.
    if (
      $this->featureFlag
        ->isActive(
          'order-history-caching'
        )
    ) {
      return $this->pgSQLRepository
        ->fetchHistory($order);
    }

    $cacheKey = '...';

    return $this->cache
      ->fetchAndStoreIfUnknown(
        $cacheKey,
        function () {
          return $this->pgSQLRepository
                   ->fetchHistory($order);
        },
        '12h' // ttl
      );
  }
}
```

Why is that safe? Well, `isActive()` will by default return
`false` which would make your code fallback to its original
execution flow. While `isActive()` keeps returning `false`
you're 100% out of danger.

With this we can tag, deploy and be confident nothing
unexpected will occur.

### Activating our feature

@TODO explain how to activate the feature flag

### Would this prevent the bug from happening?

**Absolutely not!**

Feature flags aren't about preventing your issues from happening.

Feature flags are about handling your failures with ease, unblocking
your from unexpected behaviours and situations.

The above code is as buggy as the last one. But with one click you
can stop the chaos and bring your operations team back to work while
you solve this bug without such pressure on you.

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
  "headline": "Using Feature Flags in PHP with Flipt and gRPC",
  "description": "You're probably already familiar with the Tag & Deployment way of releasing code. In this post I'll show you a much safer manner to release code in a reliable way with Feature Flags.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/14-snake-640.webp"
   ],
  "datePublished": "2020-04-12T00:00:00+08:00",
  "dateModified": "2020-04-12T00:00:00+08:00",
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
