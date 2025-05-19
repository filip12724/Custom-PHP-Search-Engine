<?php 
declare (strict_types=1);

namespace App\index;

use App\util\documentReader;
use App\index\contracts\IndexInterface;
use App\index\contracts\TokenizerInterface;

final class indexBuilder 
{
    public function __construct(
        private documentReader $reader,
        private TokenizerInterface $tokenizer,
        private IndexInterface $index
    ){}

    public function rebuild(): void{

        $this->index->clear();

       $globalPostings = [];

        foreach ($this->reader->getAll() as $doc) {
            $docId = $doc['id'];
            $termsWithFreq = $this->tokenizer->countTerms($doc['body']);

            foreach ($termsWithFreq as $term => $freq) {
                
                $globalPostings[$term][] = [
                    'id'   => $docId,
                    'freq' => $freq,
                ];
            }
        }

        
        foreach ($globalPostings as $term => $docsArray) {
            $this->index->store($term, $docsArray);
        }

    }
}



?>