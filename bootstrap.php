<?php

use Nawarian\ThePHPWebsite\Domain\Job\JobRepository;
use Nawarian\ThePHPWebsite\Domain\Rss\Feed;
use Nawarian\ThePHPWebsite\FetchJobOpportunities;
use Nawarian\ThePHPWebsite\Infrastructure\Domain\Job\GithubIssueJobRepository;
use Nawarian\ThePHPWebsite\PostQualityVerifier;
use Nawarian\ThePHPWebsite\RssGenerator;
use Nawarian\ThePHPWebsite\SearchIndexGenerator;
use TightenCo\Jigsaw\Collection\CollectionItem;
use TightenCo\Jigsaw\Jigsaw;
use PODEntender\SitemapGenerator\Adapter\Jigsaw\JigsawAdapter;
use TightenCo\Jigsaw\PageVariable;

/** @var $container \Illuminate\Container\Container */
/** @var $events \TightenCo\Jigsaw\Events\EventBus */

/**
 * You can run custom code at different stages of the build process by
 * listening to the 'beforeBuild', 'afterCollections', and 'afterBuild' events.
 *
 * For example:
 *
 * $events->beforeBuild(function (Jigsaw $jigsaw) {
 *     // Your code here
 * });
 */
$container->bind(JobRepository::class, GithubIssueJobRepository::class);

//$events->beforeBuild(function (Jigsaw $app) {
//    $manager = $app->app->make(Intervention\Image\ImageManager::class);
//    $files = $app->getFilesystem()->files('source/assets/images/posts');
//
//    /** @var SplFileInfo $file */
//    foreach ($files as $file) {
//        if ($file->getBasename() !== 'filename.extension') {
//            continue;
//        }
//
//        $outputPath = dirname($file->getRealPath());
//        $manager->make($file->getRealPath())
//            ->widen(640)
//            ->save("{$outputPath}/{$file->getBasename('.' . $file->getExtension())}-640.webp");
//    }
//});

$events->beforeBuild(function (Jigsaw $app) {
    // (POC) Generate job opportunities for pt-br pages
    if ($app->getConfig('production') === true) {
        try {
            $app->app->make(FetchJobOpportunities::class)->execute();
        } catch (Exception $e) {
            // Bypass exception
        }
    }
});

$events->afterCollections(function (Jigsaw $app) {
    $app->getCollection('posts_en')
        ->groupBy('category')
        ->each(function (PageVariable $episodes, string $category) use ($app) {
            $app->getSiteData()->put($category . '_en', $episodes);

            $episodes->each(function (CollectionItem $item) use ($episodes) {
                $recommendations = $episodes->filter(function ($episode) use ($item) {
                    return $episode !== $item;
                })->take(3);

                $item->set('recommendations', $recommendations);
            });
        });

    $app->getCollection('posts_pt_br')
        ->groupBy('category')
        ->each(function (PageVariable $episodes, string $category) use ($app) {
            $app->getSiteData()->put($category . '_pt_br', $episodes);

            $episodes->each(function (CollectionItem $item) use ($episodes) {
                $recommendations = $episodes->filter(function ($episode) use ($item) {
                    return $episode !== $item;
                })->take(3);

                $item->set('recommendations', $recommendations);
            });
        });

    $app->setConfig(
        'featuredPublication',
        $app->getCollection('posts_en')
            ->filter(function (PageVariable $page) use ($app) {
                return $page->get('isFeatured') ?? false;
            })
            ->sortByDesc('createdAt')
            ->first()
    );

    $app->setConfig(
        'featuredPublicationBr',
        $app->getCollection('posts_pt_br')
            ->filter(function (PageVariable $page) use ($app) {
                return $page->get('isFeatured') ?? false;
            })
            ->sortByDesc('createdAt')
            ->first()
    );

    $app->setConfig(
        'latestIssues',
        $app->getCollection('posts_en')
            ->filter(function (PageVariable $page) use ($app) {
                return $page !== $app->getConfig('featuredPublication');
            })
    );

    $app->setConfig(
        'latestIssuesBr',
        $app->getCollection('posts_pt_br')
            ->filter(function (PageVariable $page) use ($app) {
                return $page !== $app->getConfig('featuredPublicationBr');
            })
    );
    $app->setConfig('latestJobsBr', $app->getCollection('jobs_pt_br')->take(12));
});

// Sitemap
$events->afterBuild(function (Jigsaw $jigsaw) {
    $outputPath = $jigsaw->getDestinationPath();

    // Fetches the adapter from Dependency Injection
    $sitemapGenerator = $jigsaw->app->get(JigsawAdapter::class);

    // English posts
    $englishPosts = $jigsaw->getCollection('posts_en');
    file_put_contents($outputPath . '/en/sitemap.xml', $sitemapGenerator->fromCollection($englishPosts)->saveXML());

    // Portuguese posts
    file_put_contents($outputPath . '/br/sitemap.xml', $sitemapGenerator->fromCollection(
        $jigsaw->getCollection('posts_pt_br')
    )->saveXML());

    // Portuguese jobs
    file_put_contents($outputPath . '/br/sitemap_vagas.xml', $sitemapGenerator->fromCollection(
        $jigsaw->getCollection('jobs_pt_br')
    )->saveXML());
});

// RSS JSON feed
$events->afterBuild(function (Jigsaw $jigsaw) {
    $outputPath = $jigsaw->getDestinationPath();

    $feedGenerator = $jigsaw->app->get(RssGenerator::class);

    // English posts
    $englishPosts = $jigsaw->getCollection('posts_en');
    $englishFeed = $feedGenerator->fromCollection($englishPosts, 'en');
    file_put_contents(
        $outputPath . '/en/feed.json',
        $englishFeed->toJsonFeedFormat(Feed::JSON_FEED_V1)
    );
    file_put_contents(
        $outputPath . '/en/feed.xml',
        $englishFeed->toAtomFeedFormat(Feed::RSS_FEED_V2)
    );

    // Portuguese posts + jobs
    $portuguesePosts = $jigsaw->getCollection('posts_pt_br');
    $portugueseFeed = $feedGenerator->fromCollection($portuguesePosts, 'pt-br');
    $portugueseJobs = $jigsaw->getCollection('jobs_pt_br');
    $portugueseJobsFeed = $feedGenerator->fromCollection($portugueseJobs, 'pt-br');

    file_put_contents(
        $outputPath . '/br/feed.json',
        $portugueseFeed->toJsonFeedFormat(Feed::RSS_FEED_V2)
    );
    file_put_contents(
        $outputPath . '/br/feed.xml',
        $portugueseFeed->toAtomFeedFormat(Feed::RSS_FEED_V2)
    );
    file_put_contents(
        $outputPath . '/br/feed-vagas.xml',
        $portugueseJobsFeed->toAtomFeedFormat(Feed::RSS_FEED_V2)
    );
});

// Search Index
$events->afterBuild(function (Jigsaw $jigsaw) {
    $destination = $jigsaw->getDestinationPath();
    /** @var SearchIndexGenerator $generator */
    $generator = $jigsaw->app->make(SearchIndexGenerator::class);

    $output = $generator->generateIndex(
        $jigsaw->getCollection('posts_en')
            ->values()
            ->merge($jigsaw->getCollection('posts_pt_br')->values())
    );

    file_put_contents(
        $destination . '/search-index.json',
        $output
    );
});

// SEO Quality Gate
$events->afterBuild(function (Jigsaw $jigsaw) {
    $destination = $jigsaw->getDestinationPath();
    $verifier = $jigsaw->app->make(PostQualityVerifier::class);

    $validateDirectory = function (string $directory) use ($verifier) {
        $directoryIterator = new DirectoryIterator($directory);

        try {
            foreach ($directoryIterator as $postDirectory) {
                $path = realpath($postDirectory->getRealPath() . '/index.html');
                if ($directoryIterator->isDot() === true || $path === false) {
                    continue;
                }

                $verifier->verify(file_get_contents($path));
            }
        } catch (Exception $e) {
            throw new Exception(
                "[SEO Quality Gate] Panicked on file '{$path}'.",
                $e->getCode(),
                $e
            );
        }
    };

    $validateDirectory($destination . DIRECTORY_SEPARATOR . '/br/edicao/');
    $validateDirectory($destination . DIRECTORY_SEPARATOR . '/en/issue/');
});
