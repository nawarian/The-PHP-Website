<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

use DateTimeInterface;

class Job
{
    private $id;

    private $title;

    private $createdAt;

    private $rawBody;

    public function __construct(string $id, string $title, DateTimeInterface $createdAt, string $rawBody)
    {
        $this->id = $id;
        $this->title = $title;
        $this->createdAt = $createdAt;
        $this->rawBody = $rawBody;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function createdAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }
}
