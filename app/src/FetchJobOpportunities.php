<?php

namespace Nawarian\ThePHPWebsite;

use DateTime;
use Illuminate\Support\Str;

class FetchJobOpportunities
{
    private const GITHUB_REPO_URL = 'https://api.github.com/repos/phpdevbr/vagas/issues?state=open&page=1';

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
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, self::GITHUB_REPO_URL);
        curl_setopt($handler, CURLOPT_USERAGENT, 'cURL');
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($handler);

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

# {$opportunity['title']}

{$opportunity['body']}
STR;
    }

    private function storeMdContent($opportunity, $content): void
    {
        $slug = Str::slug($opportunity['id'] . ' ' . $opportunity['title'], '-', 'br');

        $path = __DIR__ . '/../../source/_jobs_pt_br/' . $slug . '.md';
        file_put_contents($path, $content);
    }
}
