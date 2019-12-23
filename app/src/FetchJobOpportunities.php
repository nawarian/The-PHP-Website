<?php

namespace Nawarian\ThePHPWebsite;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nawarian\ThePHPWebsite\Domain\Job\JobMdSerializer;
use Nawarian\ThePHPWebsite\Domain\Job\JobRepository;

class FetchJobOpportunities
{
    private $fs;

    private $jobRepository;

    private $jobSerializer;

    public function __construct(Filesystem $fs, JobRepository $jobRepository, JobMdSerializer $jobSerializer)
    {
        $this->fs = $fs;
        $this->jobRepository = $jobRepository;
        $this->jobSerializer = $jobSerializer;
    }

    public function execute(): void
    {
        $jobs = $this->jobRepository->fetch(30, 0);

        foreach ($jobs as $job) {
            $content = $this->jobSerializer->serialize($job);
            $slug = $job->slug();
            $path = realpath(__DIR__ . '/../../source/_jobs_pt_br/');

            $this->fs->put($path . '/' . $slug . '.md', $content);
        }
    }
}
