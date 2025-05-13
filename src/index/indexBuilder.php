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

        $postings = [];
        foreach($this->reader->getAll() as $doc){
            $id = $doc['id'];
            $terms = $this->tokenizer->countTerms($doc['body']);

            foreach($terms as $term => $freq){
                $this->index->store($term, [['id' => $id, 'freq' => $freq]]); 
            }
        }


    }
}



?>