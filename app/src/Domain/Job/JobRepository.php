<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Domain\Job;

interface JobRepository
{
    /**
     * @param int $limit
     * @param int $offset
     * @return JobCollection&Job[]
     */
    public function fetch(int $limit, int $offset): JobCollection;
}
