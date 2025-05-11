<?php 
declare (strict_types=1);

namespace App\index\tokenizer;

use App\index\contracts\TokenizerInterface;

class tokenizer implements TokenizerInterface{

    public function countTerms(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        $words = preg_split('/\P{L}+/u',$text,-1,PREG_SPLIT_NO_EMPTY);
        $counts = [];

        foreach($words as $word){
            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }
        return $counts;    
    }

}

?>