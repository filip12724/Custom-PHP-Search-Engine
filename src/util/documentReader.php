<?php 
declare (strict_types=1);
namespace App\util;


class documentReader{

    public function __construct(
        private string $baseDir = __DIR__
    ){}

 public function getAll(): iterable
{
    $it = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
            $this->baseDir,
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
        )
    );

    foreach ($it as $file) {
        $filename = $file->getFilename();
        // Extract ID from the first 8 characters of filename
        $hexId = substr($filename, 0, 8);
        $id = hexdec($hexId);
        
        // Ensure valid ID and filename format
        if ($file->isFile() && $file->getSize() > 0 && strspn($hexId, '0123456789abcdef') === 8) {
            try {
                $data = file_get_contents($file->getPathname());
                $document = unserialize($data);
                
                if ($this->validateDocument($document)) {
                    yield [
                        'id' => $id,
                        'url' => $document[0],
                        'body' => $document[1],
                        'title' => $document[0] // Use URL as title
                    ];
                }
            } catch (\Exception $e) {
                error_log("Error reading {$filename}: " . $e->getMessage());
                continue;
            }
        }
    }
}

public function getById(int $id): ?array
{
    $hexId = str_pad(dechex($id), 8, '0', STR_PAD_LEFT);
    $filename = $hexId . '*.ser'; 

    foreach (glob($this->baseDir . '/' . $filename) as $file) {
        $data = file_get_contents($file);
        $doc = @unserialize($data);
        
        if ($this->validateDocument($doc)) {
            return [
                'id' => $id,
                'url' => $doc[0],
                'body' => $doc[1],
                'title' => $doc[0] 
            ];
        }
    }
    
    return null;
}
private function validateDocument(array $document): bool
{
    return count($document) === 2 
        && is_string($document[0]) 
        && is_string($document[1]);
}
}


?>