<?php

// Development-only: inspect public image references on the catalogued official sites.
require dirname(__DIR__).'/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Symfony\Component\DomCrawler\Crawler;

$root = dirname(__DIR__);
$catalog = json_decode(file_get_contents($root.'/database/catalogs/hospitals-2026-09.json'), true, 512, JSON_THROW_ON_ERROR);
$client = new Client(['timeout' => 18, 'connect_timeout' => 7, 'headers' => ['User-Agent' => 'AsianHealthConnect directory asset review']]);
$results = [];
foreach ($catalog['groups'] as $group) {
    if (isset($argv[1]) && ! in_array($group['slug'], explode(',', $argv[1]), true)) {
        continue;
    }
    try {
        $url = $argv[2] ?? $group['website'];
        $html = (string) $client->get($url)->getBody();
        $crawler = new Crawler($html, $url);
        if (isset($argv[3])) {
            echo $crawler->filter($argv[3])->each(fn (Crawler $node) => $node->outerHtml())[0] ?? 'No match';

            continue;
        }
        $images = $crawler->filter('img')->each(function (Crawler $node) use ($group) {
            $src = $node->attr('data-src') ?: $node->attr('src');

            return ['url' => $src ? (string) UriResolver::resolve(new Uri($group['website']), new Uri($src)) : '', 'alt' => $node->attr('alt')];
        });
        $logos = array_values(array_filter($images, fn ($img) => preg_match('/logo|brand/i', $img['url'].' '.$img['alt'])));
        $results[$group['slug']] = array_slice($logos ?: $images, 0, 8);
    } catch (Throwable $e) {
        $results[$group['slug']] = ['error' => $e->getMessage()];
    }
    echo $group['slug'].': '.json_encode($results[$group['slug']], JSON_UNESCAPED_SLASHES).PHP_EOL;
}
