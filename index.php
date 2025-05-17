<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use App\Search\SimpleSearch;
use App\Index\storage\FlatFileIndex;
use App\Index\tokenizer\Tokenizer;
use App\Util\DocumentReader;


$reader    = new DocumentReader(__DIR__ . '/documents');
$tokenizer = new Tokenizer();
$index     = new FlatFileIndex(__DIR__ . '/data/index');
$totalDocs = (int) @file_get_contents(__DIR__ . '/data/index/totalDocs.txt');


$searchSvc = new SimpleSearch($tokenizer, $index, $reader, $totalDocs);

$q = $_GET['q'] ?? '';
$q = strip_tags($q);

$page = max(1, (int) ($_GET['page'] ?? 1));

$results = $searchSvc->search($q, $page, 10);
?>

<!DOCTYPE html>
<html>
<head><title>Search: <?=htmlspecialchars($q)?></title></head>
<body>
  <form><input name="q" value="<?=htmlspecialchars($q)?>"><button>Search</button></form>

  
  <ul>
  <?php foreach ($results['results'] as $r): ?>
    <li>
      <?php if (empty($r)): ?>
      <em>MISSING DOCUMENT (ID: <?= $r['id'] ?? 'unknown' ?>)</em>
    <?php else: ?>
      <strong><?=htmlspecialchars($r['title'])?></strong>
      <em>(score: <?= $r['score'] ?>)</em>
      <p><?=htmlspecialchars($r['snippet'])?></p>
        <?php endif; ?>
    </li>
  <?php endforeach; ?>
  </ul>

  <?php if ($results['total'] > 10): ?>
    <nav>
      <?php for ($p = 1; $p <= ceil($results['total']/10); $p++): ?>
        <a href="?q=<?=urlencode($q)?>&page=<?=$p?>"><?=$p?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
</body>
</html>
