<?php

// Convert generated originals without changing their composition or removing the source files.
$manifest = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$output = __DIR__.'/../public/images/treatments';
if (! is_dir($output)) {
    mkdir($output, 0755, true);
}
foreach ($manifest as $entry) {
    if (! preg_match('/^[a-z-]+$/', $entry['key'])) {
        throw new RuntimeException('Invalid illustration key.');
    }
    $image = imagecreatefrompng($entry['source']);
    $resized = imagescale($image, 640, 640, IMG_BICUBIC_FIXED);
    $path = $output.'/'.$entry['key'].'.webp';
    if (! imagewebp($resized, $path, 82)) {
        throw new RuntimeException('Could not write '.$path);
    }
    imagedestroy($image);
    imagedestroy($resized);
    echo $entry['key'].': '.filesize($path)." bytes\n";
}
