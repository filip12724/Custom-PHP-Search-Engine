<?php 
declare (strict_types=1);

namespace App\search;

interface searchInterface{

    public function search(string $query, int $page = 1, int $perPage = 10 ) :array;
    
}
?>