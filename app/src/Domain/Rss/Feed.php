<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Rss;

use DOMDocument;

class Feed
{
    public const JSON_FEED_V1 = 'https://jsonfeed.org/version/1';
    public const RSS_FEED_V2 = '2.0';

    private $userComment;

    private $title;

    private $description;

    private $homePageUrl;

    private $feedUrl;

    private $author;

    private $items;

    public function __construct(
        $userComment,
        $title,
        $description,
        $homePageUrl,
        $feedUrl,
        Author $author,
        ItemCollection $items
    ) {
        $this->userComment = $userComment;
        $this->title = $title;
        $this->description = $description;
        $this->homePageUrl = $homePageUrl;
        $this->feedUrl = $feedUrl;
        $this->author = $author;
        $this->items = $items;
    }

    public function toJsonFeedFormat(string $version): string
    {
        $items = $this->items->map(function (Item $item) {
            return [
                'title' => $item->title(),
                'date_published' => $item->datePublished()->format(DATE_RFC3339),
                'id' => $item->id(),
                'url' => $item->url(),
                'content_html' => $item->contentHtml(),
            ];
        })->values()->toArray();

        return json_encode([
            'version' => $version,
            'user_comment' => $this->userComment,
            'title' => $this->title,
            'description' => $this->description,
            'home_page_url' => $this->homePageUrl,
            'feed_url' => $this->feedUrl,
            'author' => [
                'name' => $this->author->name(),
                'url' => $this->author->url(),
            ],
            'items' => $items,
        ]);
    }

    public function toAtomFeedFormat(string $version): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', $version);
        $channel = $dom->createElement('channel');
        $channel->appendChild(
            $dom->createElement('title', $this->title)
        );
        $channel->appendChild(
            $dom->createElement('description', $this->description)
        );
        $channel->appendChild(
            $dom->createElement('link', $this->homePageUrl)
        );

        $this->items->each(function (Item $feedItem) use ($dom, $channel) {
            $item = $dom->createElement('item');
            $item->appendChild(
                $dom->createElement('title', $feedItem->title())
            );
            $item->appendChild(
                $dom->createElement('description', $feedItem->contentHtml())
            );
            $item->appendChild(
                $dom->createElement('link', $feedItem->url())
            );
            $item->appendChild(
                $dom->createElement('guid', $feedItem->id())
            );
            $item->appendChild(
                $dom->createElement(
                    'pubDate',
                    $feedItem->datePublished()->format(\DateTimeInterface::RFC3339)
                )
            );

            $channel->appendChild($item);
        });

        $rss->appendChild($channel);
        $dom->appendChild($rss);

        return trim($dom->saveXML());
    }
}
