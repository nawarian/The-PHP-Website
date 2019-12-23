<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;


use DateTime;
use PHPUnit\Framework\TestCase;

class JobMdSerializerTest extends TestCase
{
    public function testSerialize()
    {
        $createdAt = new DateTime();
        $job = new Job('1', 'my title', $createdAt, 'my body');

        $serializer = new JobMdSerializer();
        $result = $serializer->serialize($job);

        $expectedMd = <<<STR
---
slug: 1-my-title
lang: pt-br
createdAt: {$createdAt->format('Y-m-d')}
title: 'my title'
sitemap:
  lastModified: {$createdAt->format('Y-m-d')}
meta:
  description: 'my title'
  twitter:
    card: summary
    site: '@nawarian'
---

my body
STR;

        self::assertEquals($expectedMd, $result);
    }
}
