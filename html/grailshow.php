<?php
require 'functions.php';
require 'config.php';

if (!array_key_exists($currUser, $authorized)) {
    header('Location: index.php'); exit;
}

$queryR   = isset($_GET['realm'])  ? intval($_GET['realm'])  : 3;
$queryHC  = isset($_GET['hc'])     ? intval($_GET['hc'])     : 0;
$queryLD  = isset($_GET['ladder']) ? intval($_GET['ladder']) : 1;
$queryEXP = isset($_GET['exp'])    ? intval($_GET['exp'])    : 1;

$name = isset($_GET['name']) ? trim($_GET['name']) : '';
if ($name === '') { header('Location: index.php'); exit; }

$cnt = isset($_GET['cnt']) ? max(1, min(999, intval($_GET['cnt']))) : 1;

$packName = '~grailshow_' . preg_replace('/[^a-zA-Z0-9_]/', '', $currUser) . '.txt';
$packPath = __DIR__ . DIRECTORY_SEPARATOR . 'savedlist' . DIRECTORY_SEPARATOR . $packName;

$line = json_encode(array('keyword' => strtolower($name), 'occu' => (string)$cnt));
file_put_contents($packPath, $line);

header("Location: index.php?realm=$queryR&hc=$queryHC&ladder=$queryLD&exp=$queryEXP&autopack=" . urlencode($packName));
exit;
