<?php 

$inFile = __DIR__ . '/top-1m/top-1m.csv';
$outFile = 'urllist.txt';

$reader = new SplFileObject($inFile);
$reader->setFlags(SplFileObject::READ_CSV);
$reader->setCsvControl(',');

$writer = new SplFileObject($outFile, 'w');

$seen = [];

while(! $reader->eof()){
    
    $row = $reader->fgetcsv();

    if(count($row) < 2){
        continue;
    }   
    [$rank,$url] = $row;

    if (preg_match('/^\d+$/',$rank) 
        && $url !== 'Hidden profile'
        && ! isset($seen[$url]) 
    ){
        $seen[$url] = true;
        $writer->fwrite("{$rank} http://{$url}/\n");
    }

}
?>