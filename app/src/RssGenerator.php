<?php

declare(strict_types=1);

namespace Nawarian\ThePHPWebsite;

use DateTimeImmutable;
use Illuminate\Support\Collection;
use Nawarian\ThePHPWebsite\Domain\Rss\Author;
use Nawarian\ThePHPWebsite\Domain\Rss\Item;
use Nawarian\ThePHPWebsite\Domain\Rss\ItemCollection;
use Nawarian\ThePHPWebsite\Domain\Rss\Feed;
use TightenCo\Jigsaw\PageVariable;

class RssGenerator
{
    public function fromCollection(Collection $collection, string $language): Feed
    {
        $isPtBr = $language === 'pt-br';
        $author = new Author('Níckolas Da Silva', 'https://thephp.website/');
        $items = $collection->map(function (PageVariable $page) {
            $htmlContent = $page->get('meta')['description'];
            $htmlContent .= " <a href='{$page->getUrl()}'>Read More...</a>";

            return new Item(
                $page->get('title'),
                DateTimeImmutable::createFromFormat('U', (string) $page->get('createdAt')),
                $page->getUrl(),
                $page->getUrl(),
                $htmlContent
            );
        })->toArray();

        return new Feed(
            '',
            'thePHP Website',
            '',
            $isPtBr ? 'https://thephp.website/br/' : 'https://thephp.website/',
            $isPtBr ? 'https://thephp.website/br/feed.json' : 'https://thephp.website/en/feed.json',
            $author,
            new ItemCollection($items)
        );
    }
}
