<?php

declare (strict_types=1);

namespace App\index\storage;

use App\index\contracts\IndexInterface;

final class flatFileIndex implements IndexInterface
{
    private string $dir;
    private array $handles = [];

    public function __construct(string $dir)
    {
        $this->dir = $dir;

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }

        for ($i = 0; $i < 256; $i++) {
            $filename = sprintf('index_%d.idx', $i);
            $path = $this->dir . DIRECTORY_SEPARATOR . $filename;
            $this->handles[$i] = fopen($path, 'ab');
        }
    }

    public function clear(): void
    {

        foreach ($this->handles as $fp) {
            @fclose($fp);
        }
        $this->handles = [];

        foreach (glob($this->dir . DIRECTORY_SEPARATOR . '*.idx') as $file) {
            @unlink($file);
        }

        for ($i = 0; $i < 256; $i++) {
            $filename = sprintf('index_%d.idx', $i);
            $path = $this->dir . DIRECTORY_SEPARATOR . $filename;
            $this->handles[$i] = fopen($path, 'ab');
        }
    }

    public function store(string $term, array $documents): bool
    {
        $bucket = crc32($term) % 256;
        $fp = $this->handles[$bucket];

        
        fwrite($fp, pack('i', strlen($term)));
        fwrite($fp, $term);

        fwrite($fp, pack('i', count($documents)));
        foreach ($documents as $doc) {
            fwrite($fp, pack('i*', $doc['id'], $doc['freq']));
        }

        return true;
    }

    public function fetch(string $term): array
    {
        $bucket = crc32($term) % 256;
        $filename = sprintf('index_%d.idx', $bucket);
        $path = $this->dir . DIRECTORY_SEPARATOR . $filename;

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
            }

            $offset += $docCount * 8;
        }

        return [];
    }

    public function __destruct()
    {
        foreach ($this->handles as $fp) {
            @fclose($fp);
        }
    }
}
