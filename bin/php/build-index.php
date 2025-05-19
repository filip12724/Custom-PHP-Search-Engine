<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\util\documentReader;
use App\index\storage\flatFileIndex;
use App\index\tokenizer\tokenizer;
use App\index\indexBuilder;

echo "[DEBUG] Starting script on " . date('c') . "\n";
$docsDir  = realpath(__DIR__ . '/../../documents');
$indexDir = __DIR__ . '/../../data/index';

echo "[DEBUG] docsDir: "; var_dump($docsDir);
echo "[DEBUG] indexDir before mkdir: {$indexDir}\n";
if (!is_dir($indexDir)) {
    mkdir($indexDir, 0755, true) || die("[ERROR] mkdir failed\n");
}
$indexDir = realpath($indexDir);
echo "[DEBUG] indexDir after realpath: "; var_dump($indexDir);


echo "[DEBUG] Instantiating reader/tokenizer/index/builder\n";
$reader    = new documentReader($docsDir);
$tokenizer = new tokenizer();
$index     = new flatFileIndex($indexDir);
$builder   = new indexBuilder($reader, $tokenizer, $index);


echo "[DEBUG] Testing DocumentReader::getAll()…\n";
foreach ($reader->getAll() as $doc) {
    echo "[DEBUG] First doc loaded in " . sprintf('%.2f', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . " sec\n";
    var_dump(array_slice($doc, 0, 2)); 
    break;
}


echo "[DEBUG] Counting files with SPL iterator…\n";
$countStart = microtime(true);
$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docsDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$totalDocs = 0;
foreach ($rii as $file) {
    if ($file->isFile()) {
        $totalDocs++;
        if ($totalDocs % 10000 === 0) {
            echo "[DEBUG] — Counted {$totalDocs} files so far…\n";
            flush();
        }
    }
}
$countDur = microtime(true) - $countStart;
echo "[DEBUG] Total files = {$totalDocs} in " . sprintf('%.2f', $countDur) . " sec\n";


file_put_contents("{$indexDir}/totalDocs.txt", (string)$totalDocs)
    or die("[ERROR] Could not write totalDocs.txt\n");
echo "[DEBUG] Wrote totalDocs.txt\n";


echo "[DEBUG] Starting full index rebuild…\n";
$buildStart = microtime(true);



$builder->rebuild();

$buildDur = microtime(true) - $buildStart;
echo "\n[DEBUG] Rebuild completed in " . sprintf('%.2f', $buildDur) . " sec\n";
echo "[DEBUG] Script finished on " . date('c') . "\n";
