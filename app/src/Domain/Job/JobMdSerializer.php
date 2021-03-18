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
title: '{$job->title()} - Vaga de Emprego'
sitemap:
  lastModified: {$job->createdAt()->format('Y-m-d')}
meta:
  description: 'Detalhes sobre a vaga de emprego: {$this->fetchDescription($job)}'
  twitter:
    card: summary
    site: '@nawarian'
---

# {$job->title()}

{$job->rawBody()}

Fonte: {$job->source()}
STR;
    }

    private function fetchDescription(Job $job): string
    {
        if (Str::contains($job->rawBody(), '## Descrição da vaga')) {
            $part = Str::after($job->rawBody(), '## Descrição da vaga');
            $part = Str::before($part, '##');

            $description = trim(str_replace(["\r\n", PHP_EOL, '  '], ' ', $part));
            return str_replace("'", '"', $description);
        }

        return $job->title();
    }
}
