<?php 
declare(strict_types=1);

namespace App\index\contracts;
interface TokenizerInterface
{
    public function countTerms(string $text): array;
}

?>