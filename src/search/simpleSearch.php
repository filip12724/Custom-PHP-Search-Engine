<?php 
declare (strict_types=1);

namespace App\search;

use App\index\contracts\IndexInterface;
use App\index\contracts\TokenizerInterface;
use App\util\documentReader;

final class simpleSearch implements searchInterface{

    public function __construct(
        private TokenizerInterface $tokenizer,
        private IndexInterface $index,
        private documentReader $reader,
        private int $totalDocs
    ){}

    public function search(string $query, int $page = 1, int $perPage = 10): array
    {
        
        $terms = $this->cleanAndCount($query);
        if (empty($terms)) {
            return ['total' => 0, 'results' => []];
        }

        
        $scores = [];   
        foreach ($terms as $term => $qFreq) {
            $postings = $this->index->fetch($term);
            $df       = count($postings);
            if ($df === 0) {
                continue;
            }
            $idf = log($this->totalDocs / $df);
            foreach ($postings as $post) {
                $docId = $post['id'];
                $tf    = $post['freq'];
                
                $scores[$docId] = ($scores[$docId] ?? 0.0) + ($tf * $idf * $qFreq);
            }
        }

        if (empty($scores)) {
            return ['total' => 0, 'results' => []];
        }

        
        arsort($scores);
        $totalHits = count($scores);
        $slice     = array_slice($scores, ($page - 1) * $perPage, $perPage, true);

        
        $results = [];
        foreach ($slice as $docId => $score) {
            $doc = $this->reader->getById($docId);
            $text = $doc['body'];
            
            $snippet = $this->makeSnippet($text, array_keys($terms));
            $results[] = [
                'id'      => $docId,
                'score'   => round($score, 4),
                'title'   => $doc['title'] ?? "Doc #{$docId}",
                'snippet' => $snippet,
            ];
        }

        return [
            'total'   => $totalHits,
            'results' => $results,
        ];
    }

    private function cleanAndCount(string $q): array{
        $clean = mb_strtolower($q);
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', trim($clean));

        if($clean === ''){
            return [];
        }
        $tokens = explode(' ', $clean);
        return array_count_values($tokens);
    }

    private function makeSnippet(string $text, array $terms, int $length = 150): string
    {
        $lower = mb_strtolower($text);
        $pos   = null;
        foreach ($terms as $t) {
            $p = mb_stripos($lower, $t);
            if ($p !== false && ($pos === null || $p < $pos)) {
                $pos = $p;
            }
        }
        if ($pos === null) {
            return mb_substr($text, 0, $length) . '…';
        }
        $start = max(0, $pos - 30);
        $snippet = mb_substr($text, $start, $length);
        return ($start > 0 ? '…' : '') . $snippet . '…';
    }
}

?>