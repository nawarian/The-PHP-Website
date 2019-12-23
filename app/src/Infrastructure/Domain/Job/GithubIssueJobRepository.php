<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Infrastructure\Domain\Job;


use DateTime;
use GuzzleHttp\Client;
use Nawarian\ThePHPWebsite\Domain\Job\Job;
use Nawarian\ThePHPWebsite\Domain\Job\JobCollection;
use Nawarian\ThePHPWebsite\Domain\Job\JobRepository;

class GithubIssueJobRepository implements JobRepository
{
    private const GITHUB_REPO_URL = 'https://api.github.com/repos/%s/issues?state=%s&page=%d';

    private const DEFAULT_REPOS = ['phpdevbr/vagas'];

    private $http;

    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    public function fetch(int $limit, int $offset): JobCollection
    {
        $result = [];
        foreach (self::DEFAULT_REPOS as $repo) {
            $url = sprintf(self::GITHUB_REPO_URL, $repo, 'open', $offset + 1);
            $result = array_merge(
                $result,
                json_decode(
                    $this->http->get($url)
                        ->getBody()
                        ->getContents(),
                    true
                )
            );
        }

        return (new JobCollection($result))
            ->map(function (array $item) {
                return new Job(
                    (string) $item['id'],
                    (string) $item['title'],
                    new DateTime($item['created_at']),
                    (string) $item['body']
                );
            });
    }
}
