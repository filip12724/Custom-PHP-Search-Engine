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
        if ($file->isFile() && $file->getSize() > 0) {
            try {
                $data = file_get_contents($file->getPathname());
                $document = unserialize($data);
                
                if ($this->validateDocument($document)) {
                    yield [
                        'id' => hexdec(substr($file->getFilename(), 0, 8)),
                        'url' => $document[0],
                        'body' => $document[1]
                    ];
                }
            } catch (\Exception $e) {
                var_dump($e);
                continue;
            }
        }
    }
}


public function getById(int $id): ?array
    {
        $hexId = str_pad(dechex($id), 8, '0', STR_PAD_LEFT);

        
        foreach (new \FilesystemIterator($this->baseDir) as $file) {
            if (! $file->isFile()) {
                continue;
            }
            if (str_starts_with($file->getFilename(), $hexId)) {
                $data = file_get_contents($file->getPathname());
                $doc  = @unserialize($data);
                if (is_array($doc) && $this->validateDocument($doc)) {
                    return [
                        'id'    => $id,
                        'url'   => $doc[0],
                        'body'  => $doc[1],
                    ];
                }
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