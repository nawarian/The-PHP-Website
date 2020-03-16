---
slug: messagepack-vs-json-benchmark
title: Comparing JSON and MessagePack
category: walkthrough
createdAt: 2020-03-15
sitemap:
  lastModified: 2020-03-15
image:
  url: /assets/images/posts/11-messagepack-640.webp
  alt: 'A photograph of different CPU chips.'
tags:
  - curiosity
  - benchmark
  - serialization
  - json
meta:
  description:
    MessagePack wins pretty much every test. But the
    difference is so little I can’t see many benefits
    on migrating from JSON to MessagePack. Interesting
    results might appear on PHP 8 with JIT though.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/messagepack-vs-json-benchmark/)

## TL;DR

Yes, technically MessagePack wins pretty much every test.
But the difference is so little **I can’t see many benefits
on migrating from JSON to MessagePack.**

Might make sense to use it from beginning, though.
**MessagePack is slightly faster and lighter than json.**

**Using MessagePack to exchange data between browser and
server doesn’t seem to make sense in my opinion.** The
response length decreases by very little when gzipped
(a few bytes), but your network debugging gets harder as
your response is now binary encoded. On the other hand,
this might make your api less prone to be consumed by
bots given this serialization format is not so common.

[You can find the benchmark code and raw numbers in this repository I created.](https://github.com/nawarian/msgpack-bm)

**Quick note:** This article looks terrible on mobile
screens. Sorry about that, but I need to present tables
to show my data.
[Ping me on twitter if you have a better way for representing this data instead of tables.](https://twitter.com/nawarian)

## What is MessagePack?

As described in the [MessagePack's official website](https://msgpack.org/)
it is like JSON, but fast and small.

In other words, **MessagePack is a serialization format
that transforms data structures into binary strings.**

The reason why it is so efficient is that structures
are mapped with very short binary stream notation.
It’s output has nearly half the size of a JSON’s.

The official website’s example compares the same data
structure represented in both JSON and MessagePack
formats. The example used in the home page shows a
map containing two entries: `compact = true` and
`schema = 0`.

The **JSON encoded** version of such map has `27 bytes`,
while **MessagePack encodes** it into `18 bytes` only.

```php
// Map used as official
// example
$json = [
  "compact" => true,
  "schema" => 0,
];

// 27 bytes
json_encode($json);

// 18 bytes
msgpack_pack($json);
```

I learned about this format very recently and by accident,
reading a [tweet from @eminetto](https://twitter.com/eminetto/status/1237510796948758535)
, but apparently it exists since 2012 or so.

It intrigued me a lot, since I work with a high traffic
applications that exchange lots of data in JSON format
with different services in the back-end. Smells like a
low hanging fruit for performance improvement.

**I decided then to benchmark MessagePack’s PHP C
extension against the native PHP’s JSON extension.**

## The benchmarking environment

To test this I set up a very simple
[repository containing three different benchmark files](https://github.com/nawarian/msgpack-bm).
One file tests msgpack’s serialization, the seconds
tests json serialization with “assoc” option set to false
and the third one serializes into json with “assoc”
option set to true.

To execute such benchmarks I chose to use Travis CI, since
pretty much anyone can check the numbers and reproduce the
tests. In short, these are the environment details I
collected from my travis executions:

- CPU: Intel(R) Xeon(R); 1 @ 2,8 GHz; Cache 33 MB
- RAM: 7,79 GB
- OS: linux/amd64 (Ubuntu 16.04.6 LTS - Xenial)
- PHP Version: 7.4.3
- MsgPack Version: 2.1.0

In a near future I will upgrade such benchmarks to use
PHP 8’s JIT.
[As I wrote in another post about how JIT works](/en/issue/php-8-jit/),
the Just In Time compiler can speed up CPU-bound operations quite a lot.

The entity I used to serialize/deserialize is a real
response from github issues API. It has 2321 lines and
147 KB of length. Sounds to me like a decent example to
represent a real-world application response.

**You can check out the entity here:**
[https://github.com/nawarian/msgpack-bm/blob/master/github-issues.json](https://github.com/nawarian/msgpack-bm/blob/master/github-issues.json).

## MessagePack is faster and lighter than JSON

As you can see, I hate hiding information. I can
tell right away that **MessagePack outperformed
JSON in every single test.**

But the difference is actually quite small. See
for yourself…

### Output sizes:

Talking about APIs, one of the most important
aspects is the message body length being transported
over the Network. The raw values are quite impressive,
but any good developer knows that in most cases
**we should compress their API responses using filters
like gzip or brotli.**

So for this comparison I decided to show the content
length encoded in both formats and gzipped as well.

Here you find the comparison table:

Format | Encoded (bytes) | Encoded + Gzipped (bytes)
------ | --------------- | -------------------------
JSON | 143025 | 26214
MessagePack | 120799 (-22226) | 26074 (-140)

As you can see when no compression filter is applied,
MessagePack is about 22 KB lighter than JSON. But
when we apply gzip in both values, MessagePack still
wins but the difference is only 140 bytes.
**Not expressive.**

### Serialization/Deserialization Times:

The other important bit of message serialization
is how long it takes to serialize that format and
deserialize it. For this test I decided to
**serialize and deserialize the same entity multiple
times** and take notes of their memory usage and
**processing time.**

The memory usage doesn’t seem to change in such
test type unless you decode the same entity 1 Million
times, which I hope is not common for most php
applications. Therefore, **I won’t present the memory
usage numbers as in this benchmark the variation
was 0 bytes.**

While collecting JSON numbers, **I found that
deserializing with the assoc option equals to true
is slightly faster in comparison with assoc equals
to false.** Which is quite interesting and kind of
make sense.

Since assoc true yield faster results for JSON, I’ll
use them in our next comparison table. 

Here it goes:

Loops | JSON Encoding (s) | MessagePack Encoding (s) | JSON Decoding (s) |  MessagePack Decoding (s)
----- | ----------------- | ------------------------ | ----------------- | ------------------------
1 | 0.00064 | 0.00019 (-0,00045) | 0.00164 | 0.00051 (-0,00113)
10 | 0.00340 | 0.00082 (-0,00258) | 0.00866 | 0.00194 (-0,00672)
100 | 0.03135 | 0.00732 (-0,02403) | 0.07905 | 0.01700 (-0,06205)
1000 | 0.30385 | 0.07250 (-0,23135) | 0.77422 | 0.16785 (-0,60637)
10000 | 3.02723 | 0.72503 (-2,95472) | 7.74523 | 1.65804 (-6,08719)
100000 | 30.29353 | 7.25324 (-23,04029) | 77.48423 | 16.71792 (-60,76631)

The **loops** number here means how many times we
executed the same operation. Being the operation a
`json_encode`, `msgpack_pack`, `json_decode` or
`msgpack_unpack`.

Personally I’d pay attention to the numbers from 1
to 100 loops. Above that number, it seems to get
unrealistic to me. I left them there anyways though,
the results start getting very interesting from 10k
loops on.

As you could notice, the differences are quite low
for the first loops.

When a single encoding operation is called,
**MessagePack is faster by 0,45 ms.** Not expressive.
When the number of encoding operations grow to 100,
the difference start being noticeable being
**MessagePack able to save 24 ms in comparison with
json.**

Decoding operations usually are slower for both
formats, but MessagePack wins here again. When
performing a single decode operation,
**MessagePack is faster by 1 ms.** While 100
decode operations execute **62 ms faster with
MessagePack** instead of json.

Even though the decoding difference is big
enough when 100 items need to be decoded, I
believe for most applications it is very unlikely
to happen. A number of operations between 1 and 10
is quite plausible for me and **MessagePack yielded
2 ms savings while encoding and 6 ms while decoding
an entity 10 times.**

Good numbers but not very expressive.

## Should I migrate from Json to MessagePack?

When it comes to software engineering, the only proper
answer is: **depends.** Every application comes with
different backgrounds and challenges.

For example, if you’re exchanging files among
different systems and compressing your content is
not an option then MessagePack can be great for saving
disk space or reducing the load on a stream operation.

An application communicating with microservices on
the back-end might benefit from MessagePack’s speed
savings if the amount of microservices per request
is superior to 10.

I suspect (even though I didn’t test this)
encoding/decoding MessagePack using JavaScript
might be slightly slower in comparison with json,
since MessagePack doesn’t run as part of the
JavaScript engine (Node, V8). So possibly
Front-End applications wouldn’t benefit from MessagePack
just yet.

Besides that, debugging responses from the Network tab
would get super annoying. On the other hand, this might
be an easy way avoid crawlers as the format is not so common.

As any other benchmark, this one is quite useless
if you’re searching for an easy to use information.
You’ll have to adapt this to your scenario and see
how it behaves.

Luckily enough migrating from one to another should
be as simple as changing a function call from
`json_encode` to `msgpack_pack` and from `json_decode`
to `msgpack_unpack`. In case of communicating
with microservices, a simple `Accept` header should
do the trick as well.

Of course the bigger the amount of points to refactor,
the more it costs to implement and test such changes.
Make sure you evaluate the possible gains you’ll get
before trying to move all your services and consumers
to refactor.

To me a 30 minutes work for 2 ms savings seem to be
fair enough. Spending 3 weeks for the same 2 ms
savings don’t seem to make sense at all. At least
not on the scale I’m used to work with.

**Using MessagePack from the beginning seems to make
sense, though.** As it overperforms json in every
single test. So if you’re developing something
brand-new, consider MessagePack.

---

Don’t forget sharing this with your geeky friends and
colleagues. I’m certain MessagePack will be a good
option for many of them.

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
  "headline": "Comparing JSON and MessagePack",
  "description": "MessagePack wins pretty much every test. But the difference is so little I can’t see many benefits on migrating from JSON to MessagePack. Interesting results might appear on PHP 8 with JIT though.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/11-messagepack-640.webp"
   ],
  "datePublished": "2020-03-15T00:00:00+08:00",
  "dateModified": "2020-03-15T00:00:00+08:00",
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
