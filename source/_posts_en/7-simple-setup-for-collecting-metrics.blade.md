---
slug: integrating-php-and-grafana
title: Integrating PHP and Grafana
createdAt: 2020-02-10
sitemap:
  lastModified: 2020-02-10
image:
  url: /assets/images/posts/7-integrating-php-grafana-640.webp
  alt: 'A computer screen showing multiple charts and data tables'
tags:
  - analytics
  - tutorial
  - grafana
meta:
  description:
    Monitoring and collecting metrics is crazy important
    for software maintainance and Grafana is a great Open
    Source tool for such. Let's glue PHP and Grafana together!
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/integrando-php-grafana/)

Hey, this is a tutorial. There's no TL;DR, sorry about that.

Within this little piece of content, I'll show you:

- What is Grafana
- A basic docker-compose set up for Grafana + PHP
- How to send metrics from PHP to Grafana
- Closing thoughts on what we should measure

Move on! Move on! Move on!

## What is Grafana?

Grafana is an Open Source tool that fetches data from
multiple data sources and lets you combine them freely
into the form of charts, tables, alerts...

It is a great monitoring tool for any kind of metrics!
From server related stuff like service timings and error
rates to business metrics like revenue per thousand views.

Whenever people come to me stating that it saves much
more time and money investing on third-party services
to monitor their applications, it just gets clearer to
me how their tech and business are just not aligned.

Adding something like Grafana to your project's toolset
is a long-term investiment that shows concrete returns
since day one. (I wish I was, but I'm not being paid
for this...)

## Playground: Grafana and PHP with docker-compose

Let's jump into the code!

But before we start, **make sure you have Docker installed
on your local machine, and that the `docker-compose`
command line application is available to you.**

```bash
$ docker-compose --version
docker-compose version <whatever version>, build <build>
```

Also make sure your docker daemon is running. I always
forget starting mine until I run `docker-compose up`...

Good! Seems like we're ready! Let's quickly create our
new project's directory and add a `docker-compose.yml`
file to its root:

```
$ mkdir 7-integrating-php-and-grafana
$ cd 7-integrating-php-and-grafana
$ touch docker-compose.yml
```

This directory (and the code) is, by the way,
[available here](https://github.com/nawarian/The-PHP-Website/tree/master/code/7-integrating-php-and-grafana).

Beautiful!

In this docker compose we're going to set three
services. A Grafana server, a Graphite server and
A PHP FPM application.

### Adding Grafana to docker-compose

Starting with Grafana only, In your brand-new
`docker-compose.yml` file let's push the following:

```yaml
version: '3'
services:
  grafana:
    image: grafana/grafana:5.4.3
    ports:
      - 3333:3000
```

Awesome! We have a Grafana 5.4.3 server running
on localhost:3333!

If you type in this address to your browser's adress
bar, Grafana's login screen will show up: http://localhost:3333.

You username and password are, as they should
be anywhere else, `admin` and `admin`.
Please log in:

**Username:** admin

**Password:** admin

Next screen, Grafana will just ask you for a
new password (I wonder why). Write a password
you'll remember and let's proceed with this thing!


### Adding Graphite to docker-compose

Remember when I told you Grafana can read from
many data sources? Graphite is one of them.

This image we're using comes with a great tool
written for using it, called StatsD.

There are many other data sources like InfluxDB,
CloudWatch or even Elasticsearch. But honestly...
let's code!

Add the following to your `docker-compose.yml` file.
Also notice I've added `networks` to grafana service:

```yaml
version: '3'
services:
  grafana:
    image: grafana/grafana:5.4.3
    ports:
      - 3333:3000
    networks:
      - grafana-net
  graphite:
    image: graphiteapp/graphite-statsd
    restart: always
    networks:
      - grafana-net
networks:
  grafana-net:

```

## Pushing metrics from PHP to Grafana

## Measure ... everything!

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
  "headline": "Integrating PHP and Grafana",
  "description": "Monitoring and collecting metrics is crazy important for software maintainance and Grafana is a great Open Source tool for such. Let's glue PHP and Grafana together!",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/7-integrating-php-grafana-640.webp"
   ],
  "datePublished": "2020-02-10T00:00:00+08:00",
  "dateModified": "2020-02-10T00:00:00+08:00",
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

