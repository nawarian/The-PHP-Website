<?php

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

$events->afterCollections(function (Jigsaw $app) {
    $app->setConfig('latestIssues', $app->getCollection('posts_en')->take(5));
});

$events->afterBuild(function (Jigsaw $jigsaw) {
    $outputPath = $jigsaw->getDestinationPath();

    // Fetches the adapter from Dependency Injection
    $sitemapGenerator = $jigsaw->app->get(JigsawAdapter::class);

    // English posts
    $englishPosts = $jigsaw->getCollection('posts_en');
    file_put_contents($outputPath . '/sitemap-en.xml', $sitemapGenerator->fromCollection($englishPosts)->saveXML());

    // Portuguese posts
    $portuguesePosts = $jigsaw->getCollection('posts_pt_br');
    file_put_contents($outputPath . '/sitemap-pt-br.xml', $sitemapGenerator->fromCollection($portuguesePosts)->saveXML());
});

