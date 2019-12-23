<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

use Illuminate\Support\Str;

class JobMdSerializer implements JobSerializer
{
    public function serialize(Job $job): string
    {
        $slug = Str::slug($job->id() . ' ' . $job->title(), '-', 'br');

        return <<<STR
---
slug: {$slug}
lang: pt-br
createdAt: {$job->createdAt()->format('Y-m-d')}
title: '{$job->title()}'
sitemap:
  lastModified: {$job->createdAt()->format('Y-m-d')}
meta:
  description: '{$job->title()}'
  twitter:
    card: summary
    site: '@nawarian'
---

{$job->rawBody()}
STR;
    }
}
