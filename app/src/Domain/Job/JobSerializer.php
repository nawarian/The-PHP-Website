<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

interface JobSerializer
{
    public function serialize(Job $job): string;
}
