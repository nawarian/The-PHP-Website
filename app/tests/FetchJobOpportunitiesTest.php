<?php

namespace Nawarian\ThePHPWebsite;

use DateTime;
use Illuminate\Filesystem\Filesystem;
use Nawarian\ThePHPWebsite\Domain\Job\Job;
use Nawarian\ThePHPWebsite\Domain\Job\JobCollection;
use Nawarian\ThePHPWebsite\Domain\Job\JobMdSerializer;
use Nawarian\ThePHPWebsite\Domain\Job\JobRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class FetchJobOpportunitiesTest extends TestCase
{
    private $fetchJobOpportunities;

    private $fileStore;

    private $jobRepository;

    public function setUp(): void
    {
        $this->fileStore = $this->prophesize(Filesystem::class);
        $this->jobRepository = $this->prophesize(JobRepository::class);

        $this->fetchJobOpportunities = new FetchJobOpportunities(
            $this->fileStore->reveal(),
            $this->jobRepository->reveal(),
            new JobMdSerializer()
        );
    }

    public function testExecute(): void
    {
        $this->jobRepository->fetch(30, 0)
            ->willReturn(
                new JobCollection([
                    new Job('541152404', '[Remoto] PHP Developer na VLabs', new DateTime(), 'mabody', 'ma-source'),
                ])
            );

        $path = realpath(__DIR__ . '/../../source/_jobs_pt_br');

        $this->fileStore->put($path . '/541152404-remoto-php-developer-na-vlabs.md', Argument::any())
            ->shouldBeCalledOnce();

        $this->fetchJobOpportunities->execute();
    }
}
