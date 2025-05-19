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
echo "<p>DEBUG: totalDocs read = {$totalDocs}</p>";

$searchSvc = new SimpleSearch($tokenizer, $index, $reader, $totalDocs);

$q    = $_GET['q']    ?? '';
$q    = strip_tags($q);
$page = max(1, (int) ($_GET['page'] ?? 1));

// *** DEBUG: show tokens for the query
$tokens = $tokenizer->tokenize($q);
echo '<p>DEBUG: tokens = <code>' 
   . (empty($tokens) ? '(none)' : implode(', ', $tokens)) 
   . '</code></p>';

   $tokens = $tokenizer->tokenize($q);
echo '<p>DEBUG: tokens = <code>'
   . (empty($tokens) ? '(none)' : implode(', ', $tokens))
   . '</code></p>';

// DEBUG: pull raw postings for each token
$postingsDebug = [];
foreach ($tokens as $tok) {
    $postingsDebug[$tok] = $index->fetch($tok);
}
echo '<pre style="background:#fee; padding:10px; margin:10px 0;">';
echo "DEBUG: raw postings for each token\n";
print_r($postingsDebug);
echo '</pre>';

$results = $searchSvc->search($q, $page, 10);


echo '<pre style="background:#f0f0f0; padding:10px; margin:10px 0;">';
echo "QUERY    = " . var_export($q, true)    . "\n";
echo "PAGE     = " . var_export($page, true) . "\n\n";
echo "RAW RESULTS:\n";
var_dump($results);
echo '</pre>';
exit;
?>
<!DOCTYPE html>
<html>
<head><title>Search: <?=htmlspecialchars($q)?></title></head>
<body>
  <form><input name="q" value="<?=htmlspecialchars($q)?>"><button>Search</button></form>
  <p><?= $results['total'] ?> results found.</p>
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
