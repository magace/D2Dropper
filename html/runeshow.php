<?php
require 'functions.php';
require 'config.php';

if (!array_key_exists($currUser, $authorized)) {
    header('Location: index.php'); exit;
}

$queryR   = isset($_POST['realm'])  ? intval($_POST['realm'])  : 3;
$queryHC  = isset($_POST['hc'])     ? intval($_POST['hc'])     : 0;
$queryLD  = isset($_POST['ladder']) ? intval($_POST['ladder']) : 1;
$queryEXP = isset($_POST['exp'])    ? intval($_POST['exp'])    : 1;

$rawRunes = isset($_POST['runes']) ? $_POST['runes'] : '';
$runes = array_filter(array_map('trim', explode(',', $rawRunes)));

if (empty($runes)) { header('Location: index.php'); exit; }

$qty = isset($_POST['qty']) ? max(1, min(99, intval($_POST['qty']))) : 1;

$runeCounts = array();
foreach ($runes as $rune) {
    $key = strtolower($rune) . ' rune';
    $runeCounts[$key] = isset($runeCounts[$key]) ? $runeCounts[$key] + 1 : 1;
}

$packName = '~runesearch_' . preg_replace('/[^a-zA-Z0-9_]/', '', $currUser) . '.txt';
$packPath = __DIR__ . DIRECTORY_SEPARATOR . 'savedlist' . DIRECTORY_SEPARATOR . $packName;

$lines = array();
foreach ($runeCounts as $keyword => $occu) {
    $lines[] = json_encode(array('keyword' => $keyword, 'occu' => (string)($occu * $qty)));
}
file_put_contents($packPath, implode("\n", $lines));

header("Location: index.php?realm=$queryR&hc=$queryHC&ladder=$queryLD&exp=$queryEXP&autopack=" . urlencode($packName));
exit;
