<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite\Infrastructure\Domain\Job;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GithubIssueJobRepositoryTest extends TestCase
{
    private $githubIssueJobRepository;

    private $http;

    protected function setUp(): void
    {
        $this->http = $this->prophesize(Client::class);

        $this->githubIssueJobRepository = new GithubIssueJobRepository(
            $this->http->reveal()
        );
    }

    public function testFetch(): void
    {
        $response = '[{"url":"https://api.github.com/repos/phpdevbr/vagas/issues/518","repository_url":"https://api.github.com/repos/phpdevbr/vagas","labels_url":"https://api.github.com/repos/phpdevbr/vagas/issues/518/labels{/name}","comments_url":"https://api.github.com/repos/phpdevbr/vagas/issues/518/comments","events_url":"https://api.github.com/repos/phpdevbr/vagas/issues/518/events","html_url":"https://github.com/phpdevbr/vagas/issues/518","id":541152404,"node_id":"MDU6SXNzdWU1NDExNTI0MDQ=","number":518,"title":"[Remoto] PHP Developer na VLabs","user":{"login":"carolina-am","id":49108517,"node_id":"MDQ6VXNlcjQ5MTA4NTE3","avatar_url":"https://avatars3.githubusercontent.com/u/49108517?v=4","gravatar_id":"","url":"https://api.github.com/users/carolina-am","html_url":"https://github.com/carolina-am","followers_url":"https://api.github.com/users/carolina-am/followers","following_url":"https://api.github.com/users/carolina-am/following{/other_user}","gists_url":"https://api.github.com/users/carolina-am/gists{/gist_id}","starred_url":"https://api.github.com/users/carolina-am/starred{/owner}{/repo}","subscriptions_url":"https://api.github.com/users/carolina-am/subscriptions","organizations_url":"https://api.github.com/users/carolina-am/orgs","repos_url":"https://api.github.com/users/carolina-am/repos","events_url":"https://api.github.com/users/carolina-am/events{/privacy}","received_events_url":"https://api.github.com/users/carolina-am/received_events","type":"User","site_admin":false},"labels":[],"state":"open","locked":false,"assignee":null,"assignees":[],"milestone":null,"comments":0,"created_at":"2019-12-20T19:05:52Z","updated_at":"2019-12-20T19:05:52Z","closed_at":null,"author_association":"NONE","body":"mabody"}]';

        $httpStream = $this->prophesize(StreamInterface::class);
        $httpStream->getContents()->willReturn($response);

        $httpResponse = $this->prophesize(ResponseInterface::class);
        $httpResponse->getBody()->willReturn($httpStream);

        $this->http->get('https://api.github.com/repos/phpdevbr/vagas/issues?state=open&page=1')
            ->willReturn($httpResponse);
        $this->http->get('https://api.github.com/repos/backend-br/vagas/issues?state=open&page=1')
            ->willReturn($httpResponse);
        $this->http->get('https://api.github.com/repos/frontendbr/vagas/issues?state=open&page=1')
            ->willReturn($httpResponse);

        $result = $this->githubIssueJobRepository->fetch(30, 0);

        self::assertCount(3, $result);
        self::assertEquals('[Remoto] PHP Developer na VLabs', $result->first()->title());
        self::assertEquals('https://github.com/phpdevbr/vagas/issues/518', $result->first()->source());
    }
}

