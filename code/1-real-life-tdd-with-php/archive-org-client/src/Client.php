<?php

namespace Nawarian\ArchiveOrg;

use Nawarian\ArchiveOrg\Item\Metadata;

class Client
{
    public function fetchMetadata(string $identifier): object
    {
        $jsonData = file_get_contents("https://archive.org/metadata/{$identifier}");
        $decoded = json_decode($jsonData, true);
        $metadata = $decoded['metadata'];

        return new Metadata(
            $metadata['identifier'],
            $metadata['publicdate'],
            $metadata['collection']
        );
    }
}
