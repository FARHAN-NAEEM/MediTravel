<?php

// Development-only asset preparation. Deployment uses the bundled files, never remote downloads.
require dirname(__DIR__).'/vendor/autoload.php';

use GuzzleHttp\Client;

$root = dirname(__DIR__);
$sources = json_decode(file_get_contents($root.'/database/catalogs/hospital-logo-sources.json'), true, 512, JSON_THROW_ON_ERROR);
$catalogPath = $root.'/database/catalogs/hospitals-2026-09.json';
$catalog = json_decode(file_get_contents($catalogPath), true, 512, JSON_THROW_ON_ERROR);
$client = new Client(['timeout' => 20, 'connect_timeout' => 8, 'headers' => ['User-Agent' => 'AsianHealthConnectDirectory/1.0 (+https://asianhealthconnect.com)']]);
$directory = $root.'/public/images/hospital-logos';
if (! is_dir($directory)) {
    mkdir($directory, 0755, true);
}
foreach ($catalog['groups'] as &$group) {
    $slug = $group['slug'];
    if (! isset($sources[$slug]) || (isset($argv[1]) && ! in_array($slug, explode(',', $argv[1]), true))) {
        continue;
    }
    try {
        $bytes = (string) $client->get($sources[$slug])->getBody();
        if (strlen($bytes) > 8000000) {
            throw new RuntimeException('Image exceeds asset limit');
        }
        if (preg_match('/<svg\b/i', $bytes)) {
            if (preg_match('/<script|<foreignObject|\bon[a-z]+\s*=|<!ENTITY|(?:href|url)\s*[=(]\s*[\x22\x27]?\s*(?:https?:|javascript:|data:text)/i', $bytes)) {
                throw new RuntimeException('SVG requires manual safety review');
            }
            $extension = 'svg';
            file_put_contents($directory.'/'.$slug.'.svg', $bytes);
        } else {
            $image = @imagecreatefromstring($bytes);
            if (! $image) {
                throw new RuntimeException('Response is not a supported image');
            }
            if (imagesx($image) > 800 || imagesy($image) > 500) {
                $ratio = min(800 / imagesx($image), 500 / imagesy($image));
                $image = imagescale($image, (int) round(imagesx($image) * $ratio), (int) round(imagesy($image) * $ratio));
            }
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $extension = 'webp';
            imagewebp($image, $directory.'/'.$slug.'.webp', 92);
        }
        $group['catalog_logo'] = 'images/hospital-logos/'.$slug.'.'.$extension;
        echo $slug.' saved'.PHP_EOL;
    } catch (Throwable $e) {
        echo $slug.' FAILED: '.$e->getMessage().PHP_EOL;
    }
}
unset($group);
file_put_contents($catalogPath, json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
