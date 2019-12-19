<?php

use Nawarian\ThePHPWebsite\FetchJobOpportunities;
use TightenCo\Jigsaw\Jigsaw;
use PODEntender\SitemapGenerator\Adapter\Jigsaw\JigsawAdapter;

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

$events->beforeBuild(function (Jigsaw $app) {
    // (POC) Generate job opportunities for pt-br pages
    $app->app->make(FetchJobOpportunities::class)->execute();
});

$events->afterCollections(function (Jigsaw $app) {
    $app->setConfig('latestIssues', $app->getCollection('posts_en')->take(5));
    $app->setConfig('latestIssuesBr', $app->getCollection('posts_pt_br')->take(5));
    $app->setConfig('latestJobsBr', $app->getCollection('jobs_pt_br')->take(10));
});

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
            ->merge($jigsaw->getCollection('jobs_pt_br'))
    )->saveXML());
});
