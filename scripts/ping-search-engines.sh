#!/bin/bash

SITEMAP_EN=https://thephp.website/en/sitemap.xml
SITEMAP_BR=https://thephp.website/br/sitemap.xml

curl -o /dev/null http://www.bing.com/webmaster/ping.aspx?sitemap=$SITEMAP_EN
curl -o /dev/null http://www.bing.com/webmaster/ping.aspx?sitemap=$SITEMAP_BR

curl -o /dev/null http://www.google.com/webmasters/sitemaps/ping?sitemap=$SITEMAP_EN
curl -o /dev/null http://www.google.com/webmasters/sitemaps/ping?sitemap=$SITEMAP_BR

