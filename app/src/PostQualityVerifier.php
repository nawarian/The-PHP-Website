<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite;

use Exception;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class PostQualityVerifier
{
    private const MIN_INTERNAL_LINKS = 2;

    private $domCrawler;

    public function __construct(Crawler $crawler)
    {
        $this->domCrawler = $crawler;
    }

    public function verify(string $html): void
    {
        $this->domCrawler->clear();
        $this->domCrawler->addContent($html);

        $this->verifyMultipleH1Tags();
        // $this->verifyInternalLinks();
    }

    private function verifyMultipleH1Tags(): void
    {
        if ($this->domCrawler->filter('article.container h1')->count() > 1) {
            throw new Exception('There should be only one h1 tag in the whole post.');
        }
    }

    private function verifyInternalLinks(): void
    {
        $pattern = 'Article contains %s internal links. At least %d are required.';
        if ($this->domCrawler->filter('article.container a')->count() === 0) {
            throw new Exception(
                sprintf($pattern, 'no', self::MIN_INTERNAL_LINKS)
            );
        }

        $found = 0;
        foreach ($this->domCrawler->filter(' article.container a') as $link) {
            $href = $link->getAttribute('href') ?? '';

            if (Str::startsWith($href, ['/', 'https://thephp.website'])) {
                $found++;
            }
        }

        if ($found < self::MIN_INTERNAL_LINKS) {
            throw new Exception(
                sprintf($pattern, $found, self::MIN_INTERNAL_LINKS)
            );
        }
    }
}
