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
<article class="container">
    <h1>the first title in the page</h1>
    <p>some content here</p>
    <h1>another title, should bring an exception</h1>
    <p>another content here</p>
</article>
STR;

        self::expectException(Exception::class);
        self::expectExceptionMessage('There should be only one h1 tag in the whole post.');

        $this->verifier->verify($multipleH1TagsHtml);
    }

    public function testVerifyErrorsIfParagraphIsTooShort(): void
    {
        self::markTestSkipped('This feature must be better thought through.');
        $shortParagraphHtml = <<<STR
<article class="container">
    <h1>the first title in the page</h1>
    <p>some content here</p>
</article>
STR;

        self::expectException(Exception::class);
        self::expectExceptionMessage(
            'Paragraphs should contain at least 65 words. '
            . "The offending paragraph was 'some content here'."
        );

        $this->verifier->verify($shortParagraphHtml);
    }

    public function testVerifyErrorsWhenNoInternalLinksAreFound(): void
    {
        self::markTestSkipped('Cant activate this feature yet.');
        $noInternalLinksHtml = <<<STR
<article class="container">
    <h1>the first title in the page</h1>
</article>
STR;

        self::expectException(Exception::class);
        self::expectExceptionMessage(
            'Article contains no internal links. At least 2 are required.'
        );

        $this->verifier->verify($noInternalLinksHtml);
    }

    public function testVerifyErrorsWhenNotEnoughInternalLinksAreFound(): void
    {
        self::markTestSkipped('Cant activate this feature yet.');
        $noInternalLinksHtml = <<<STR
<article class="container">
    <h1>the first title in the page</h1>
    <a href="/en/issue/test">Testing</a>
</article>
STR;

        self::expectException(Exception::class);
        self::expectExceptionMessage(
            'Article contains 1 internal links. At least 2 are required.'
        );

        $this->verifier->verify($noInternalLinksHtml);
    }
}
