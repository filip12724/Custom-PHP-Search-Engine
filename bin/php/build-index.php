<?php 
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\util\documentReader;
use App\index\storage\flatFileIndex;
use App\index\tokenizer\tokenizer;
use App\index\indexBuilder;

// prep
$docsDir  = realpath(__DIR__.'/../../documents');
$indexDir = __DIR__ . '/../../data/index';
if (!is_dir($indexDir)) {
    mkdir($indexDir, 0755, true);
}
$indexDir = realpath($indexDir); 


$reader    = new documentReader($docsDir);
$tokenizer = new tokenizer();
$index     = new flatFileIndex($indexDir);
$builder   = new indexBuilder($reader, $tokenizer, $index);

// start timer
$startTime = microtime(true);

// run
$totalDocs = iterator_count($reader->getAll());
file_put_contents($indexDir . '/totalDocs.txt', (string)$totalDocs);
echo "Wrote totalDocs ({$totalDocs}) before rebuild\n";

// 2) now rebuild
echo "Rebuilding index…\n";
$start = microtime(true);
$builder->rebuild();
$dur = microtime(true) - $start;
echo sprintf("Done in %.2f sec\n", $dur);
