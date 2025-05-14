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

   public function store(string $term, array $documents): bool {
        $bucket = crc32($term) % 256;
        $filename = "index_$bucket.idx";
        $path = $this->dir . '/' . $filename;

        $fp = fopen($path, 'ab');
        
        fwrite($fp, pack('i', strlen($term))); 
        fwrite($fp, $term);
        
        fwrite($fp, pack('i', count($documents))); 
        foreach ($documents as $doc) {
            fwrite($fp, pack('i*', $doc['id'], $doc['freq']));
        }
        fclose($fp);
        return true;
    }

    public function fetch(string $term): array {
        $bucket = crc32($term) % 256;
        $filename = "index_$bucket.idx";
        $path = $this->dir . '/' . $filename;

        if (!file_exists($path)) {
            return [];
        }

        $bytes = file_get_contents($path);
        $offset = 0;
        $out = [];

        while ($offset < strlen($bytes)) {
            
            $termLength = unpack('i', substr($bytes, $offset, 4))[1];
            $offset += 4;
            
            $currentTerm = substr($bytes, $offset, $termLength);
            $offset += $termLength;
            
            $docCount = unpack('i', substr($bytes, $offset, 4))[1];
            $offset += 4;

            
            if ($currentTerm === $term) {
                for ($i = 0; $i < $docCount; $i++) {
                    $id = unpack('i', substr($bytes, $offset, 4))[1];
                    $offset += 4;
                    $freq = unpack('i', substr($bytes, $offset, 4))[1];
                    $offset += 4;
                    $out[] = ['id' => $id, 'freq' => $freq];
                }
                return $out; 
            } else {
               
                $offset += $docCount * 8;
            }
        }

        return [];
    }
}

?>