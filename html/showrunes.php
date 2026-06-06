<?php
require 'functions.php';
require 'config.php';

header('Content-Type: application/json');

if (!array_key_exists($currUser, $authorized)) { echo '[]'; exit; }

$queryR   = isset($_GET['realm'])  ? intval($_GET['realm'])  : 3;
$queryHC  = isset($_GET['hc'])     ? intval($_GET['hc'])     : 0;
$queryLD  = isset($_GET['ladder']) ? intval($_GET['ladder']) : 1;
$queryEXP = isset($_GET['exp'])    ? intval($_GET['exp'])    : 1;

// classids is a comma-separated list (may contain duplicates for e.g. Ral+Ral)
$raw = isset($_GET['classids']) ? $_GET['classids'] : '';
$ids = array();
foreach (explode(',', $raw) as $v) {
    $v = intval(trim($v));
    if ($v >= 610 && $v <= 642) $ids[] = $v;
}
if (empty($ids)) { echo '[]'; exit; }

// Count how many of each classid we need
$needed = array();
foreach ($ids as $id) {
    $needed[$id] = isset($needed[$id]) ? $needed[$id] + 1 : 1;
}

$realmnames = array('uswest','useast','asia','europe');
$result  = array();
$usedIds = array();

try {
    $conn = new PDO('sqlite:ItemDB.s3db');
    foreach ($needed as $classid => $count) {
        $found = 0;
        $rows = $conn->query(
            'SELECT itemId, itemName, itemMD5, itemType, itemImage,
                    charName, accountLogin, accountRealm
             FROM muleItems
             LEFT JOIN muleChars ON itemCharId = charId
             LEFT JOIN muleAccounts ON charAccountId = accountId
             WHERE accountRealm = '.$queryR.'
             AND charHardcore  = '.$queryHC.'
             AND charLadder    = '.$queryLD.'
             AND charExpansion = '.$queryEXP.'
             AND itemClassid   = '.$classid.'
             ORDER BY itemId ASC'
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($found >= $count) break;
            if (in_array($row['itemId'], $usedIds)) continue;
            $usedIds[]  = $row['itemId'];
            $result[] = array(
                'name'     => $row['itemName'],
                'realm'    => $realmnames[intval($row['accountRealm'])],
                'account'  => $row['accountLogin'],
                'charName' => $row['charName'],
                'itemType' => $row['itemType'],
                'dropit'   => $row['itemMD5'],
                'skin'     => $row['itemImage'],
                'itemID'   => strval($row['itemId']),
            );
            $found++;
        }
    }
    $conn = null;
} catch (PDOException $e) { $conn = null; }

echo json_encode($result);
