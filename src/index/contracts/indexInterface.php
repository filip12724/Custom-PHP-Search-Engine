<?php 
declare (strict_types=1);

namespace App\index\contracts;

use InvalidArgumentException;

interface IndexInterface
{
    public function clear(): void;
    public function store(string $term, array $documents): bool;
    public function fetch(string $term): array;
}
?>