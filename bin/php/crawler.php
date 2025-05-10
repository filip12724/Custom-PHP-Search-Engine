<?php
require __DIR__ . '/../../vendor/autoload.php';

if ($argc < 2 || empty(trim($argv[1]))) {
    fwrite(STDERR, "⚠️ Empty URL received\n");
    exit(1);
}

$url = trim($argv[1]);
$max_retries = 3;
$timeout = 15; 


$md5  = md5($url);
$one  = substr($md5, 0, 2);
$two  = substr($md5, 2, 2);
$base =  dirname(__DIR__, 2) . '/documents';
$dir  = "{$base}/{$one}/{$two}";
$file = "{$dir}/{$md5}";


if (file_exists($file)) {
    exit(0);
}


$context = stream_context_create([
    'http' => [
        'timeout' => $timeout,
        'ignore_errors' => true,
        'follow_location' => true,
        'max_redirects' => 3
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);


$success = false;
for ($retry = 1; $retry <= $max_retries; $retry++) {
    try {
        $content = @file_get_contents($url, false, $context);
        $http_code = 0;
        
        if (!empty($http_response_header)) {
            $http_code = (int) explode(' ', $http_response_header[0])[1];
        }

        if ($content !== false && $http_code >= 200 && $http_code < 400) {
            $success = true;
            break;
        }
    } catch (Exception $e) {
        
        $content = false;
    }
    
    if ($retry < $max_retries) {
        sleep(pow(2, $retry)); 
    }
}


if ($success) {
   
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        fwrite(STDERR, "⛔ Directory creation failed: $dir\n");
        exit(2);
    }
    
    $document = [$url, $content];
    if (file_put_contents($file, serialize($document)) === false) {
        fwrite(STDERR, "⛔ File write failed: $file\n");
        exit(3);
    }
    
    echo "Downloaded – $url\n";
    exit(0);
}

fwrite(STDERR, "💥 Failed after $max_retries attempts: $url" . 
    ($http_code ? " (HTTP $http_code)" : "") . "\n");
exit(1);