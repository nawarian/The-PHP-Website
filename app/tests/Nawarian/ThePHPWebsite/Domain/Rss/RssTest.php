<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Rss;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class RssTest extends TestCase
{
    public function testToJsonFeedFormat(): void
    {
        $date = new DateTimeImmutable();
        $dateStr = $date->format(DateTimeInterface::RFC3339);
        $feed = $this->givenARssFeedWithTwoItemsExistForTheSameDate($date);

        $json = $feed->toJsonFeedFormat(Feed::JSON_FEED_V1);
        $expectedJson = <<<STR
{"version":"https:\/\/jsonfeed.org\/version\/1","user_comment":"","title":"thePHP Website","description":"","home_page_url":"https:\/\/thephp.website\/","feed_url":"https:\/\/thephp.website\/en\/feed.json","author":{"name":"Test","url":"test.url"},"items":[{"title":"My first item","date_published":"{$dateStr}","id":"first-item-id","url":"first.item.url","content_html":"my html content"},{"title":"My second item","date_published":"{$dateStr}","id":"second-item-id","url":"second.item.url","content_html":"my html content"}]}
STR;

        self::assertEquals($expectedJson, $json);
    }

    public function testToAtomFeedFormat(): void
    {
        $date = new DateTimeImmutable();
        $dateStr = $date->format(DateTimeInterface::RFC3339);
        $feed = $this->givenARssFeedWithTwoItemsExistForTheSameDate($date);

        $rss = $feed->toAtomFeedFormat(Feed::RSS_FEED_V2);
        $expectedXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><title>thePHP Website</title><description/><link>https://thephp.website/</link><item><title>My first item</title><description>my html content</description><link>first.item.url</link><guid>first-item-id</guid><pubDate>{$dateStr}</pubDate></item><item><title>My second item</title><description>my html content</description><link>second.item.url</link><guid>second-item-id</guid><pubDate>{$dateStr}</pubDate></item></channel></rss>
XML;

        self::assertEquals($expectedXml, $rss);
    }

    private function givenARssFeedWithTwoItemsExistForTheSameDate(DateTimeInterface $date): Feed
    {
        $author = new Author('Test', 'test.url');
        $items = new ItemCollection([
            new Item(
                'My first item',
                $date,
                'first-item-id',
                'first.item.url',
                'my html content'
            ),
            new Item(
                'My second item',
                $date,
                'second-item-id',
                'second.item.url',
                'my html content'
            ),
        ]);

        return new Feed(
            '',
            'thePHP Website',
            '',
            'https://thephp.website/',
            'https://thephp.website/en/feed.json',
            $author,
            $items
        );
    }
}
