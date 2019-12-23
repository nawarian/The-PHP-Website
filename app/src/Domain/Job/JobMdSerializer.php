<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

use Illuminate\Support\Str;

class JobMdSerializer implements JobSerializer
{
    public function serialize(Job $job): string
    {
        return <<<STR
---
slug: {$job->slug()}
lang: pt-br
createdAt: {$job->createdAt()->format('Y-m-d')}
title: '{$job->title()}'
sitemap:
  lastModified: {$job->createdAt()->format('Y-m-d')}
meta:
  description: '{$this->fetchDescription($job)}'
  twitter:
    card: summary
    site: '@nawarian'
---

{$job->rawBody()}
STR;
    }

    private function fetchDescription(Job $job): string
    {
        if (Str::contains($job->rawBody(), '## Descrição da vaga')) {
            $part = Str::after($job->rawBody(), '## Descrição da vaga');
            $part = Str::before($part, '##');

            return trim(str_replace(["\r\n", PHP_EOL, '  '], ' ', $part));
        }

        return $job->title();
    }
}
