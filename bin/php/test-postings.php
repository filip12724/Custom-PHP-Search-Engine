<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Index\storage\FlatFileIndex;
use App\Index\tokenizer\Tokenizer;

$index     = new FlatFileIndex(__DIR__ . '/../../data/index');
$tokenizer = new Tokenizer();

// change or add any tokens you want to test
$terms = ['you', 'apple', 'search'];

foreach ($terms as $term) {
    $toks = $tokenizer->tokenize($term);
    echo "Term “{$term}” → tokens: " . implode(', ', $toks) . "\n";
    foreach ($toks as $tok) {
        $postings = $index->fetch($tok);
        echo "  postings for “{$tok}”: ";
        var_export($postings);
        echo "\n";
    }
    echo str_repeat('─', 40) . "\n";
}
