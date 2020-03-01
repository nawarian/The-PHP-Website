<?php

use Nawarian\ThePHPWebsite\Domain\Job\JobRepository;
use Nawarian\ThePHPWebsite\Domain\Rss\Feed;
use Nawarian\ThePHPWebsite\FetchJobOpportunities;
use Nawarian\ThePHPWebsite\Infrastructure\Domain\Job\GithubIssueJobRepository;
use Nawarian\ThePHPWebsite\RssGenerator;
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
//        $outputPath = dirname($file->getRealPath());
//        $manager->make($file->getRealPath())
//            ->widen(640)
//            ->save("{$outputPath}/{$file->getBasename('.' . $file->getExtension())}-640.webp");
//    }
//});

$events->beforeBuild(function (Jigsaw $app) {
    // (POC) Generate job opportunities for pt-br pages
    if ($app->getConfig('production') === true) {
        $app->app->make(FetchJobOpportunities::class)->execute();
    }
});

$events->afterCollections(function (Jigsaw $app) {
    $app->setConfig(
        'latestIssues',
        $app->getCollection('posts_en')
            ->filter(function (PageVariable $page) {
                return $page->get('category') !== 'faq';
            })
            ->take(12)
    );
    $app->setConfig(
        'latestIssuesBr',
        $app->getCollection('posts_pt_br')
            ->filter(function (PageVariable $page) {
                return $page->get('category') !== 'faq';
            })
            ->take(12)
    );
    $app->setConfig('latestJobsBr', $app->getCollection('jobs_pt_br')->take(12));

    $app->getCollection('posts_en')
        ->groupBy('category')
        ->each(function (PageVariable $episodes, string $category) use ($app) {
            $app->getSiteData()->put($category . '_en', $episodes);
        });

    $app->getCollection('posts_pt_br')
        ->groupBy('category')
        ->each(function (PageVariable $episodes, string $category) use ($app) {
            $app->getSiteData()->put($category . '_pt_br', $episodes);
        });
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
    $portuguesePosts = $jigsaw->getCollection('posts_pt_br')
        ->merge($jigsaw->getCollection('jobs_pt_br'));
    $portugueseFeed = $feedGenerator->fromCollection($portuguesePosts, 'pt-br');
    file_put_contents(
        $outputPath . '/br/feed.json',
        $portugueseFeed->toJsonFeedFormat(Feed::RSS_FEED_V2)
    );
    file_put_contents(
        $outputPath . '/br/feed.xml',
        $portugueseFeed->toAtomFeedFormat(Feed::RSS_FEED_V2)
    );
});
