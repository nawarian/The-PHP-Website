<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Rss;

use DateTimeInterface;

class Item
{
    private $title;

    private $datePublished;

    private $id;

    private $url;

    private $contentHtml;

    public function __construct(
        string $title,
        DateTimeInterface $datePublished,
        string $id,
        string $url,
        string $contentHtml
    ) {
        $this->title = $title;
        $this->datePublished = $datePublished;
        $this->id = $id;
        $this->url = $url;
        $this->contentHtml = $contentHtml;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function datePublished(): DateTimeInterface
    {
        return $this->datePublished;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function contentHtml(): string
    {
        return $this->contentHtml;
    }
}
