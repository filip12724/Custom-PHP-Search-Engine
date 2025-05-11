<?php 
declare (strict_types=1);

namespace App\index\storage;

use App\index\contracts\IndexInterface;

class flatFileIndex implements IndexInterface{

    public function __construct(private string $dir) {}

    public function clear(): void
    {
        foreach (glob($this->dir . '/*.idx') as $f){
            @unlink($f);
        }
    }

    public function store(string $term, array $documents): bool
    {
        return true;
    }

    public function fetch(string $term): array
    {
        return [];
    }
}

?>