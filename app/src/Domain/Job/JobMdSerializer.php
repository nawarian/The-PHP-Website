<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

use Illuminate\Support\Str;

class JobMdSerializer implements JobSerializer
{
    public function serialize(Job $job): string
    {
        $properties = [
            'slug' => $job->slug(),
            'lang' => 'pt-br',
            'createdAt' => $job->createdAt()->format('Y-m-d'),
            'title' => $job->title(),
        ];

        $serialized = '---';
        foreach ($properties as $key => $value) {
            $serialized .= PHP_EOL;
            $serialized .= "{$key}: '{$value}'";
        }
        $serialized .= PHP_EOL;
        $serialized .= <<<STR
meta:
  description: '{$this->fetchDescription($job)}'
  twitter:
    card: summary
    site: '@nawarian'
STR;
        $labels = $this->fetchLabels($job);
        if ([] !== $labels) {
          $serialized .= PHP_EOL . 'labels:';
            foreach ($labels as $label) {
                $serialized .= PHP_EOL . '  - ' . $label;
            }
        }
        $serialized .= PHP_EOL . '---' . PHP_EOL . PHP_EOL;
        $serialized .= $job->rawBody() . PHP_EOL . PHP_EOL;
        $serialized .= 'Fonte: ' . $job->source();

        return $serialized;
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
    
    private function fetchLabels(Job $job): array
    {
        if (false === Str::contains($job->rawBody(), '## Labels')) {
            return [];
        }

        $part = Str::after($job->rawBody(), '## Labels');
        $part = Str::before($part, '##');
        $part = trim(str_replace(["\r\n", '-', '  '], ' ', $part));
        $labels = explode(PHP_EOL, $part);

        return array_map(function (string $label) {
            return trim($label);
        }, $labels);
    }
}
