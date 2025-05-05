<?php
// crawler.php

// 1) get the URL from argv
if ($argc < 2) {
    fwrite(STDERR, "Usage: php crawler.php <url>\n");
    exit(1);
}
$url = trim($argv[1]);

// 2) hash it and split into two‑level dirs
$md5  = md5($url);
$one  = substr($md5, 0, 2);
$two  = substr($md5, 2, 2);
$base = __DIR__ . '/documents';
$dir  = "{$base}/{$one}/{$two}";
$file = "{$dir}/{$md5}";

// 3) if we haven’t already saved this URL
if (! file_exists($file)) {
    echo "Downloading – {$url}\n";
    // grab the HTML (suppress warnings for 404s etc)
    $content = @file_get_contents($url);
    if ($content === false) {
        echo "  → failed to download: {$url}\n";
        exit(1);
    }
    // pack up url+content
    $document   = [$url, $content];
    $serialized = serialize($document);

    // make the two‑level folder tree in one go
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // write it out
    file_put_contents($file, $serialized);
}
