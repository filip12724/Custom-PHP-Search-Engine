<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\util\documentReader;
use App\index\storage\flatFileIndex;
use App\index\tokenizer\tokenizer;
use App\index\indexBuilder;

// prep
$docsDir  = realpath(__DIR__.'/../../documents');
$indexDir = realpath(__DIR__.'/../../data/index');
if (!is_dir($indexDir)) {
    mkdir($indexDir, 0755, true);
}

$reader    = new documentReader($docsDir);
$tokenizer = new tokenizer();
$index     = new flatFileIndex($indexDir);
$builder   = new indexBuilder($reader, $tokenizer, $index);

// start timer
$startTime = microtime(true);

// run
echo "Rebuilding index…\n";
$builder->rebuild();

// report
$duration = microtime(true) - $startTime;
$memPeak  = memory_get_peak_usage(true) / 1024 / 1024;

echo sprintf(
    "Done in %.2f seconds, peak memory: %.1f MB\n",
    $duration,
    $memPeak
);
