<?php

namespace Nawarian\ArchiveOrg\Test;

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testClientFetchesMetadata(): void
    {
        $client = new \Nawarian\ArchiveOrg\Client();

        $metadata = $client->fetchMetadata('nawarian-test');

        $this->assertSame('nawarian-test', $metadata->identifier());
        $this->assertSame('2019-02-19 20:00:38', $metadata->publicDate());
        $this->assertSame('opensource', $metadata->collection());
    }
}
