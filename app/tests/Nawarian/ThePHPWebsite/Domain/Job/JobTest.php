<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

use DateTime;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testSlug(): void
    {
        $createdAt = new DateTime();
        $job = new Job('1', 'my title', $createdAt, 'my body', 'ma source');

        self::assertEquals('1-my-title', $job->slug());
    }
}
