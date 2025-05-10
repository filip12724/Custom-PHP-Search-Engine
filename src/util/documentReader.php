<?php 
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

private function validateDocument(array $document): bool
{
    return count($document) === 2 
        && is_string($document[0]) 
        && is_string($document[1]);
}
}


?>