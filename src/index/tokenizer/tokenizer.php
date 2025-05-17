<?php 
declare (strict_types=1);

namespace App\index\tokenizer;

use App\index\contracts\TokenizerInterface;

class tokenizer implements TokenizerInterface{

 
   public function countTerms(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $counts = [];

        foreach ($words as $word) {
            // Force string type for all terms
            $wordKey = (string) $word;
            $counts[$wordKey] = ($counts[$wordKey] ?? 0) + 1;
        }

        return $counts;
    }

}

?>