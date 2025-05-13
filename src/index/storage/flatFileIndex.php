<?php 
declare (strict_types=1);

namespace App\index\storage;

use App\index\contracts\IndexInterface;
use InvalidArgumentException;

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
        if($term === ''){
            throw new InvalidArgumentException("Cannot store empty term");
        }
        $path = $this->dir . '/' . rawurlencode($term) . '.idx';

        $fp = fopen($path,'wb');

        foreach($documents as $doc){
            fwrite($fp, pack('i*', $doc['id'], $doc['freq']));
        }
        fclose($fp);
        return true;
    }

    public function fetch(string $term): array
    {
            $path = $this->dir . '/' . rawurlencode($term) . '.idx';
            if (!file_exists($path)) {
                return [];
            }
            $bytes = file_get_contents($path);
            $ints  = array_values(unpack('i*', $bytes));
            $out   = [];
            for ($i = 0; $i < count($ints); $i += 2) {
                $out[] = ['id' => $ints[$i], 'freq' => $ints[$i+1]];
            }
            return $out;
    }
}

?>