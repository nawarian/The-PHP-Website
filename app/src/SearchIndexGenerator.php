<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite;

use TightenCo\Jigsaw\Collection\CollectionItem;
use TightenCo\Jigsaw\PageVariable;

final class SearchIndexGenerator
{
    public function generateIndex(PageVariable $publications): string
    {
        $publicationsIndex = $publications->map(function (CollectionItem $publication) {
            return [
                'lang' => $publication->get('lang') ?? 'en',
                'title' => $publication->get('title'),
                'url' => $publication->getUrl(),
                'category' => $publication->get('category'),
                'createdAt' => $publication->get('createdAt'),
                'description' => $publication->get('meta', [])['description'] ?? '',
                'tags' => implode(', ', $publication->get('tags')),
            ];
        })->values()->toArray();

        return json_encode($publicationsIndex);
    }
}
