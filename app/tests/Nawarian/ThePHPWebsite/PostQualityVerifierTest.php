<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class PostQualityVerifierTest extends TestCase
{
    private $verifier;

    protected function setUp(): void
    {
        $this->verifier = new PostQualityVerifier(
            new Crawler()
        );
    }

    public function testVerifyErrorsIfMoreThanOneH1TagIsFound(): void
    {
        $multipleH1TagsHtml = <<<STR
<h1>the first title in the page</h1>
<p>some content here</p>
<h1>another title, should bring an exception</h1>
<p>another content here</p>
STR;

        self::expectException(Exception::class);
        self::expectExceptionMessage('There should be only one h1 tag in the whole post.');

        $this->verifier->verify($multipleH1TagsHtml);
    }
}
