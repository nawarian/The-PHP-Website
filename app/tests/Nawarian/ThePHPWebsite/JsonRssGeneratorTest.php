<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite;

use PHPUnit\Framework\TestCase;
use TightenCo\Jigsaw\Collection\Collection;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageVariable;

class JsonRssGeneratorTest extends TestCase
{
    private $jsonRssGenerator;

    protected function setUp(): void
    {
        $this->jsonRssGenerator = new JsonRssGenerator();
    }

    public function testFromCollection(): void
    {
        $pages = $this->givenIHaveTwoPagesCollection();
        $json = $this->jsonRssGenerator->fromCollection($pages, 'pt-br');
        $jsonArray = json_decode((string) $json, true);

        self::assertEquals('https://jsonfeed.org/version/1', $jsonArray['version']);
        self::assertEquals('thePHP Website', $jsonArray['title']);
        self::assertEquals('https://thephp.website/br/', $jsonArray['home_page_url']);
        self::assertEquals([
            'name' => 'Níckolas Da Silva',
            'url' => 'https://thephp.website/'
        ], $jsonArray['author']);
        self::assertArrayHasKey('description', $jsonArray);
        self::assertArrayHasKey('feed_url', $jsonArray);
        self::assertCount(2, $jsonArray['items']);
    }

    public function testFromCollectionChangesUrlsBasedOnLanguage(): void
    {
        $pages = $this->givenIHaveTwoPagesCollection();
        $json = $this->jsonRssGenerator->fromCollection($pages, 'pt-br');
        $jsonArray = json_decode((string) $json, true);

        self::assertEquals('https://thephp.website/br/', $jsonArray['home_page_url']);
        self::assertEquals('https://thephp.website/br/feed.json', $jsonArray['feed_url']);

        $json = $this->jsonRssGenerator->fromCollection($pages, 'en');
        $jsonArray = json_decode((string) $json, true);

        self::assertEquals('https://thephp.website/', $jsonArray['home_page_url']);
        self::assertEquals('https://thephp.website/en/feed.json', $jsonArray['feed_url']);
    }

    private function givenIHaveTwoPagesCollection(): Collection
    {
        return new Collection([
            $this->createJigsawPage('first.html', '2020-01-17'),
            $this->createJigsawPage('second.html', '2020-01-17'),
        ]);
    }

    private function createJigsawPage($url, $date = null): PageVariable
    {
        $page = [
            'extends' => '_layouts/test-base',
            'createdAt' => is_callable($date) ? null : strtotime($date),
            '_meta' => new IterableObject([
                'url' => 'https://thephp.website/' . $url,
            ]),
            'meta' => [
                'description' => 'An amazing description',
            ],
            'title' => $url,
        ];

        return new PageVariable($page);
    }
}
