<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Rss;

class JsonRss
{
    private $version;

    private $userComment;

    private $title;

    private $description;

    private $homePageUrl;

    private $feedUrl;

    private $author;

    private $items;

    public function __construct(
        $version,
        $userComment,
        $title,
        $description,
        $homePageUrl,
        $feedUrl,
        Author $author,
        ItemCollection $items
    ) {
        $this->version = $version;
        $this->userComment = $userComment;
        $this->title = $title;
        $this->description = $description;
        $this->homePageUrl = $homePageUrl;
        $this->feedUrl = $feedUrl;
        $this->author = $author;
        $this->items = $items;
    }

    public function __toString(): string
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
            'version' => $this->version,
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
}
