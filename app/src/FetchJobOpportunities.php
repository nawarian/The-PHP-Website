<?php

namespace Nawarian\ThePHPWebsite;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nawarian\ThePHPWebsite\Domain\Job\Job;
use Nawarian\ThePHPWebsite\Domain\Job\JobRepository;

class FetchJobOpportunities
{
    private $fs;

    private $jobRepository;

    public function __construct(Filesystem $fs, JobRepository $jobRepository)
    {
        $this->fs = $fs;
        $this->jobRepository = $jobRepository;
    }

    public function execute(): void
    {
        $opportunities = $this->jobRepository->fetch(30, 0);

        foreach ($opportunities as $opportunity) {
            $content = $this->transformJobOpportunityIntoMdContent($opportunity);
            $this->storeMdContent($opportunity, $content);
        }
    }

    private function transformJobOpportunityIntoMdContent(Job $job): string
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

    private function storeMdContent(Job $job, $content): void
    {
        $slug = Str::slug($job->id() . ' ' . $job->title(), '-', 'br');
        $path = realpath(__DIR__ . '/../../source/_jobs_pt_br/');

        $this->fs->put($path . '/' . $slug . '.md', $content);
    }
}
