<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite;

use Exception;
use Symfony\Component\DomCrawler\Crawler;

class PostQualityVerifier
{
    private $domCrawler;

    public function __construct(Crawler $crawler)
    {
        $this->domCrawler = $crawler;
    }

    public function verify(string $html): void
    {
        $this->domCrawler->clear();
        $this->domCrawler->addContent($html);

        $this->verifyMultipleH1Tags($html);
    }

    private function verifyMultipleH1Tags(string $html): void
    {
        if ($this->domCrawler->filter('h1')->count() > 1) {
            throw new Exception('There should be only one h1 tag in the whole post.');
        }
    }
}
