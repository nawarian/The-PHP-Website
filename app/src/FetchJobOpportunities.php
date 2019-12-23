<?php

namespace Nawarian\ThePHPWebsite;

use DateTime;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class FetchJobOpportunities
{
    private const GITHUB_REPO_URL = 'https://api.github.com/repos/phpdevbr/vagas/issues?state=open&page=1';

    private $fs;

    private $http;

    public function __construct(Filesystem $fs, Client $http)
    {
        $this->fs = $fs;
        $this->http = $http;
    }

    public function execute(): void
    {
        $opportunities = $this->fetchJobOpportunities();

        foreach ($opportunities as $opportunity) {
            $content = $this->transformJobOpportunityIntoMdContent($opportunity);
            $this->storeMdContent($opportunity, $content);
        }
    }

    private function fetchJobOpportunities()
    {
        $result = $this->http->get(self::GITHUB_REPO_URL)
            ->getBody()
            ->getContents();

        return json_decode($result, true);
    }

    private function transformJobOpportunityIntoMdContent($opportunity): string
    {
        $slug = Str::slug($opportunity['id'] . ' ' . $opportunity['title'], '-', 'br');
        $createdAt = new DateTime($opportunity['created_at']);

        return <<<STR
---
slug: {$slug}
lang: pt-br
createdAt: {$createdAt->format('Y-m-d')}
title: '{$opportunity['title']}'
sitemap:
  lastModified: {$createdAt->format('Y-m-d')}
meta:
  description: '{$opportunity['title']}'
  twitter:
    card: summary
    site: '@nawarian'
---

{$opportunity['body']}
STR;
    }

    private function storeMdContent($opportunity, $content): void
    {
        $slug = Str::slug($opportunity['id'] . ' ' . $opportunity['title'], '-', 'br');
        $path = realpath(__DIR__ . '/../../source/_jobs_pt_br/');

        $this->fs->put($path . '/' . $slug . '.md', $content);
    }
}
