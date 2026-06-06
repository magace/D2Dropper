<!DOCTYPE html>
<html lang="en">
<?php
require 'functions.php';
require 'config.php';
$themeName = getTheme($_SERVER['PHP_AUTH_USER']);

$queryR   = isset($_GET['realm'])  ? intval($_GET['realm'])  : 3;
$queryHC  = isset($_GET['hc'])     ? intval($_GET['hc'])     : 0;
$queryLD  = isset($_GET['ladder']) ? intval($_GET['ladder']) : 1;
$queryEXP = isset($_GET['exp'])    ? intval($_GET['exp'])    : 1;

$realmNames  = array(0=>'West', 1=>'East', 2=>'Asia', 3=>'Euro');
$typeNames   = array(0=>'SC', 1=>'HC');
$ladderNames = array(0=>'Non-Ladder', 1=>'Ladder');
$realmLabel  = (isset($realmNames[$queryR])  ? $realmNames[$queryR]  : 'Euro')
             . ' ' . (isset($typeNames[$queryHC])   ? $typeNames[$queryHC]   : 'SC')
             . ' ' . (isset($ladderNames[$queryLD]) ? $ladderNames[$queryLD] : 'Ladder');

$runeClassid = array(
    'El'=>610,'Eld'=>611,'Tir'=>612,'Nef'=>613,'Eth'=>614,'Ith'=>615,
    'Tal'=>616,'Ral'=>617,'Ort'=>618,'Thul'=>619,'Amn'=>620,'Sol'=>621,
    'Shael'=>622,'Dol'=>623,'Hel'=>624,'Io'=>625,'Lum'=>626,'Ko'=>627,
    'Fal'=>628,'Lem'=>629,'Pul'=>630,'Um'=>631,'Mal'=>632,'Ist'=>633,
    'Gul'=>634,'Vex'=>635,'Ohm'=>636,'Lo'=>637,'Sur'=>638,'Ber'=>639,
    'Jah'=>640,'Cham'=>641,'Zod'=>642
);

$runewords = array(
    array('name'=>"Ancient's Pledge", 'sockets'=>3, 'base'=>'Shield',                   'runes'=>array('Ral','Ort','Tal')),
    array('name'=>'Beast',             'sockets'=>5, 'base'=>'Axe / Hammer / Scepter',   'runes'=>array('Ber','Tir','Um','Mal','Lum')),
    array('name'=>'Black',             'sockets'=>3, 'base'=>'Club / Hammer / Mace',     'runes'=>array('Thul','Io','Nef')),
    array('name'=>'Bone',              'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Sol','Um','Um')),
    array('name'=>'Bramble',           'sockets'=>4, 'base'=>'Body Armor',                'runes'=>array('Ral','Ohm','Sur','Eth')),
    array('name'=>'Brand',             'sockets'=>4, 'base'=>'Missile Weapon',            'runes'=>array('Jah','Lo','Mal','Gul')),
    array('name'=>'Breath of the Dying','sockets'=>6,'base'=>'Weapon',                   'runes'=>array('Vex','Hel','El','Eld','Zod','Eth')),
    array('name'=>'Call to Arms',      'sockets'=>5, 'base'=>'Weapon',                    'runes'=>array('Amn','Ral','Mal','Ist','Ohm')),
    array('name'=>'Chains of Honor',   'sockets'=>4, 'base'=>'Body Armor',                'runes'=>array('Dol','Um','Ber','Ist')),
    array('name'=>'Chaos',             'sockets'=>3, 'base'=>'Claw',                      'runes'=>array('Fal','Ohm','Um')),
    array('name'=>'Crescent Moon',     'sockets'=>3, 'base'=>'Axe / Polearm / Sword',    'runes'=>array('Shael','Um','Tir')),
    array('name'=>'Death',             'sockets'=>5, 'base'=>'Sword / Axe',               'runes'=>array('Hel','El','Vex','Ort','Gul')),
    array('name'=>'Delirium',          'sockets'=>3, 'base'=>'Helm',                      'runes'=>array('Lem','Ist','Io')),
    array('name'=>'Destruction',       'sockets'=>5, 'base'=>'Polearm / Sword',           'runes'=>array('Vex','Lo','Ber','Jah','Ko')),
    array('name'=>'Doom',              'sockets'=>5, 'base'=>'Axe / Hammer / Polearm',   'runes'=>array('Hel','Ohm','Um','Lo','Cham')),
    array('name'=>'Dragon',            'sockets'=>3, 'base'=>'Body Armor / Shield',       'runes'=>array('Sur','Lo','Sol')),
    array('name'=>'Dream',             'sockets'=>3, 'base'=>'Helm / Shield',             'runes'=>array('Io','Jah','Pul')),
    array('name'=>'Duress',            'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Shael','Um','Thul')),
    array('name'=>'Edge',              'sockets'=>3, 'base'=>'Missile Weapon',            'runes'=>array('Tir','Tal','Amn')),
    array('name'=>'Enigma',            'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Jah','Ith','Ber')),
    array('name'=>'Enlightenment',     'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Pul','Ral','Sol')),
    array('name'=>'Eternity',          'sockets'=>5, 'base'=>'Melee Weapon',              'runes'=>array('Amn','Ber','Ist','Sol','Sur')),
    array('name'=>'Exile',             'sockets'=>4, 'base'=>'Paladin Shield',            'runes'=>array('Vex','Ohm','Ist','Dol')),
    array('name'=>'Faith',             'sockets'=>4, 'base'=>'Missile Weapon',            'runes'=>array('Ohm','Jah','Lem','Eld')),
    array('name'=>'Famine',            'sockets'=>4, 'base'=>'Axe / Hammer',              'runes'=>array('Fal','Ohm','Ort','Jah')),
    array('name'=>'Flickering Flame',  'sockets'=>3, 'base'=>'Helm',                      'runes'=>array('Nef','Pul','Vex')),
    array('name'=>'Fortitude',         'sockets'=>4, 'base'=>'Weapon / Body Armor',       'runes'=>array('El','Sol','Dol','Lo')),
    array('name'=>'Fury',              'sockets'=>3, 'base'=>'Melee Weapon',              'runes'=>array('Jah','Gul','Eth')),
    array('name'=>'Gloom',             'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Fal','Um','Pul')),
    array('name'=>'Grief',             'sockets'=>5, 'base'=>'Sword / Axe',               'runes'=>array('Eth','Tir','Lo','Mal','Ral')),
    array('name'=>'Hand of Justice',   'sockets'=>4, 'base'=>'Weapon',                    'runes'=>array('Sur','Cham','Amn','Lo')),
    array('name'=>'Harmony',           'sockets'=>4, 'base'=>'Missile Weapon',            'runes'=>array('Tir','Ith','Sol','Ko')),
    array('name'=>'Heart of the Oak',  'sockets'=>4, 'base'=>'Staff / Mace',              'runes'=>array('Ko','Vex','Pul','Thul')),
    array('name'=>'Holy Thunder',      'sockets'=>4, 'base'=>'Scepter',                   'runes'=>array('Eth','Ral','Ort','Tal')),
    array('name'=>'Honor',             'sockets'=>5, 'base'=>'Melee Weapon',              'runes'=>array('Amn','El','Ith','Tir','Sol')),
    array('name'=>'Ice',               'sockets'=>4, 'base'=>'Missile Weapon',            'runes'=>array('Amn','Shael','Jah','Lo')),
    array('name'=>'Infinity',          'sockets'=>4, 'base'=>'Polearm / Spear',           'runes'=>array('Ber','Mal','Ber','Ist')),
    array('name'=>'Insight',           'sockets'=>4, 'base'=>'Polearm / Staff',           'runes'=>array('Ral','Tir','Tal','Sol')),
    array('name'=>"King's Grace",      'sockets'=>3, 'base'=>'Sword / Scepter',           'runes'=>array('Amn','Ral','Thul')),
    array('name'=>'Kingslayer',        'sockets'=>4, 'base'=>'Sword / Axe',               'runes'=>array('Mal','Um','Gul','Fal')),
    array('name'=>'Last Wish',         'sockets'=>6, 'base'=>'Sword / Hammer / Axe',      'runes'=>array('Jah','Mal','Jah','Sur','Jah','Ber')),
    array('name'=>'Lawbringer',        'sockets'=>3, 'base'=>'Sword / Hammer / Scepter',  'runes'=>array('Amn','Lem','Ko')),
    array('name'=>'Leaf',              'sockets'=>2, 'base'=>'Staff',                     'runes'=>array('Tir','Ral')),
    array('name'=>'Lore',              'sockets'=>2, 'base'=>'Helm',                      'runes'=>array('Ort','Sol')),
    array('name'=>'Malice',            'sockets'=>3, 'base'=>'Melee Weapon',              'runes'=>array('Ith','El','Eth')),
    array('name'=>'Melody',            'sockets'=>3, 'base'=>'Missile Weapon',            'runes'=>array('Shael','Ko','Nef')),
    array('name'=>'Memory',            'sockets'=>4, 'base'=>'Staff',                     'runes'=>array('Lum','Io','Sol','Eth')),
    array('name'=>'Myth',              'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Hel','Amn','Nef')),
    array('name'=>'Nadir',             'sockets'=>2, 'base'=>'Helm',                      'runes'=>array('Nef','Tir')),
    array('name'=>'Oath',              'sockets'=>4, 'base'=>'Sword / Axe / Mace',        'runes'=>array('Shael','Pul','Mal','Lum')),
    array('name'=>'Obedience',         'sockets'=>5, 'base'=>'Polearm',                   'runes'=>array('Hel','Ko','Thul','Eth','Fal')),
    array('name'=>'Obsession',         'sockets'=>6, 'base'=>'Staff',                     'runes'=>array('Zod','Ist','Lem','Lum','Io','Nef')),
    array('name'=>'Passion',           'sockets'=>4, 'base'=>'Weapon',                    'runes'=>array('Dol','Ort','Eld','Lem')),
    array('name'=>'Peace',             'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Shael','Thul','Amn')),
    array('name'=>'Phoenix',           'sockets'=>4, 'base'=>'Weapon / Shield',           'runes'=>array('Vex','Vex','Lo','Jah')),
    array('name'=>'Pride',             'sockets'=>4, 'base'=>'Polearm',                   'runes'=>array('Cham','Sur','Io','Lo')),
    array('name'=>'Principle',         'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Ral','Gul','Eld')),
    array('name'=>'Radiance',          'sockets'=>3, 'base'=>'Helm',                      'runes'=>array('Nef','Sol','Ith')),
    array('name'=>'Rain',              'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Ort','Mal','Ith')),
    array('name'=>'Rhyme',             'sockets'=>2, 'base'=>'Shield',                    'runes'=>array('Shael','Eth')),
    array('name'=>'Rift',              'sockets'=>4, 'base'=>'Polearm / Scepter',         'runes'=>array('Hel','Ko','Lem','Gul')),
    array('name'=>'Sanctuary',         'sockets'=>3, 'base'=>'Shield',                    'runes'=>array('Ko','Ko','Mal')),
    array('name'=>'Silence',           'sockets'=>6, 'base'=>'Weapon',                    'runes'=>array('Dol','Eld','Hel','Ist','Tir','Vex')),
    array('name'=>'Smoke',             'sockets'=>2, 'base'=>'Body Armor',                'runes'=>array('Nef','Lum')),
    array('name'=>'Spirit',            'sockets'=>4, 'base'=>'Sword / Shield',            'runes'=>array('Tal','Thul','Ort','Amn')),
    array('name'=>'Splendor',          'sockets'=>2, 'base'=>'Shield',                    'runes'=>array('Eth','Lum')),
    array('name'=>'Stealth',           'sockets'=>2, 'base'=>'Body Armor',                'runes'=>array('Tal','Eth')),
    array('name'=>'Steel',             'sockets'=>2, 'base'=>'Sword / Axe / Mace',        'runes'=>array('Tir','El')),
    array('name'=>'Stone',             'sockets'=>4, 'base'=>'Body Armor',                'runes'=>array('Shael','Um','Pul','Lum')),
    array('name'=>'Strength',          'sockets'=>2, 'base'=>'Melee Weapon',              'runes'=>array('Amn','Tir')),
    array('name'=>'Treachery',         'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Shael','Thul','Lem')),
    array('name'=>'Venom',             'sockets'=>3, 'base'=>'Weapon',                    'runes'=>array('Tal','Dol','Mal')),
    array('name'=>'Voice of Reason',   'sockets'=>4, 'base'=>'Sword / Mace',              'runes'=>array('Lem','Ko','El','Eld')),
    array('name'=>'Wealth',            'sockets'=>3, 'base'=>'Body Armor',                'runes'=>array('Lem','Ko','Tir')),
    array('name'=>'White',             'sockets'=>2, 'base'=>'Wand',                      'runes'=>array('Dol','Io')),
    array('name'=>'Wind',              'sockets'=>2, 'base'=>'Melee Weapon',              'runes'=>array('Sur','El')),
    array('name'=>'Wrath',             'sockets'=>4, 'base'=>'Missile Weapon',            'runes'=>array('Pul','Lum','Ber','Mal')),
    array('name'=>'Zephyr',            'sockets'=>2, 'base'=>'Missile Weapon',            'runes'=>array('Ort','Eth')),
);

// Query rune inventory
$inventory = array();
foreach (array_keys($runeClassid) as $r) { $inventory[$r] = 0; }
try {
    $conn = new PDO('sqlite:ItemDB.s3db');
    $sql  = 'SELECT itemClassid, COUNT(*) as cnt FROM muleItems
             LEFT JOIN muleChars ON itemCharId = charId
             LEFT JOIN muleAccounts ON charAccountId = accountId
             WHERE accountRealm = '.$queryR.'
             AND charHardcore = '.$queryHC.'
             AND charLadder = '.$queryLD.'
             AND charExpansion = '.$queryEXP.'
             AND itemClassid BETWEEN 610 AND 642
             GROUP BY itemClassid';
    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;
    $classidToName = array_flip($runeClassid);
    foreach ($rows as $row) {
        if (isset($classidToName[$row['itemClassid']])) {
            $inventory[$classidToName[$row['itemClassid']]] = intval($row['cnt']);
        }
    }
} catch (PDOException $e) { $conn = null; }

// Annotate each runeword with slot colours and missing count
foreach ($runewords as &$rw) {
    $remaining = $inventory;
    $slots     = array();
    $missing   = 0;
    foreach ($rw['runes'] as $rune) {
        if ($remaining[$rune] > 0) {
            $slots[] = true;
            $remaining[$rune]--;
        } else {
            $slots[] = false;
            $missing++;
        }
    }
    $rw['slots']   = $slots;
    $rw['missing'] = $missing;
    $needed = array();
    foreach ($rw['runes'] as $rune) {
        $needed[$rune] = isset($needed[$rune]) ? $needed[$rune] + 1 : 1;
    }
    $craftable = PHP_INT_MAX;
    foreach ($needed as $rune => $cnt) {
        $craftable = min($craftable, (int)($inventory[$rune] / $cnt));
    }
    $rw['craftable'] = ($craftable === PHP_INT_MAX) ? 0 : $craftable;
}
unset($rw);

usort($runewords, function($a, $b) { return $a['missing'] - $b['missing']; });

$canMake   = 0;
foreach ($runewords as $rw) { if ($rw['missing'] === 0) $canMake++; }
$cantMake  = count($runewords) - $canMake;

$tiers = array(
    'Low'  => array('El','Eld','Tir','Nef','Eth','Ith','Tal','Ral','Ort','Thul','Amn','Sol','Shael','Dol'),
    'Mid'  => array('Hel','Io','Lum','Ko','Fal','Lem','Pul','Um','Mal','Ist','Gul'),
    'High' => array('Vex','Ohm','Lo','Sur','Ber','Jah','Cham','Zod'),
);
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="images/icons/Akara.ico">
    <title>Runeword Builder</title>
    <link id="layout1" rel="stylesheet" href="themes/<?php echo $themeName; ?>/css/bootstrap.css">
    <link id="layout2" rel="stylesheet" type="text/css" href="themes/<?php echo $themeName; ?>/css/itemManager.css">
    <style>
        .rune-badge { display:inline-block; border-radius:3px; padding:2px 7px; margin:2px; font-size:12px; font-family:Arial; }
        .rune-ok    { background:#285228; color:#18FC00; }
        .rune-miss  { background:#5a1a1a; color:#ff6666; }
        .inv-badge  { display:inline-block; border-radius:3px; padding:2px 6px; margin:2px; font-size:12px; font-family:Arial; }
        .inv-have   { background:#D08420; color:#000; }
        .inv-none   { background:#2a2a2a; color:#666; }
        #rwtable td { vertical-align:middle; }
    </style>
</head>
<body style="overflow-y:auto">

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <?php showThemes(); ?>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a class="exocet" href="grail.php?realm=<?php echo $queryR; ?>&hc=<?php echo $queryHC; ?>&ladder=<?php echo $queryLD; ?>&exp=<?php echo $queryEXP; ?>">GRAIL</a></li>
                <li><a class="exocet" href="index.php?realm=<?php echo $queryR; ?>&hc=<?php echo $queryHC; ?>&ladder=<?php echo $queryLD; ?>&exp=<?php echo $queryEXP; ?>">BACK</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">

    <!-- Rune Inventory -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">RUNE INVENTORY &mdash; <?php echo htmlspecialchars($realmLabel); ?></h3>
        </div>
        <div class="panel-body" style="padding:8px 12px">
            <?php foreach ($tiers as $tier => $runes):
                $colors = array('Low'=>'#888','Mid'=>'#D08420','High'=>'#B04434');
            ?>
            <div style="margin-bottom:4px">
                <span style="color:<?php echo $colors[$tier]; ?>;font-family:Exocet,Arial;font-size:11px;display:inline-block;width:35px"><?php echo $tier; ?></span>
                <?php foreach ($runes as $rune):
                    $cnt = $inventory[$rune];
                ?>
                <span class="inv-badge <?php echo $cnt > 0 ? 'inv-have' : 'inv-none'; ?>">
                    <?php echo $rune; ?> <strong><?php echo $cnt; ?></strong>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Status filter -->
    <div style="margin-bottom:6px">
        <button class="btn btn-default active rw-filter" data-filter="all">All (<?php echo count($runewords); ?>)</button>
        <button class="btn btn-success rw-filter" data-filter="can">Can Make (<?php echo $canMake; ?>)</button>
        <button class="btn btn-danger rw-filter" data-filter="cant">Can't Make (<?php echo $cantMake; ?>)</button>
    </div>
    <!-- Base type filter -->
    <div style="margin-bottom:10px">
        <button class="btn btn-default btn-xs active rw-base" data-base="all">All Types</button>
        <button class="btn btn-default btn-xs rw-base" data-base="armor">Armor</button>
        <button class="btn btn-default btn-xs rw-base" data-base="helm">Helm</button>
        <button class="btn btn-default btn-xs rw-base" data-base="shield">Shield</button>
        <button class="btn btn-default btn-xs rw-base" data-base="sword">Sword</button>
        <button class="btn btn-default btn-xs rw-base" data-base="axe">Axe</button>
        <button class="btn btn-default btn-xs rw-base" data-base="mace">Mace</button>
        <button class="btn btn-default btn-xs rw-base" data-base="polearm">Polearm</button>
        <button class="btn btn-default btn-xs rw-base" data-base="missile">Missile</button>
        <button class="btn btn-default btn-xs rw-base" data-base="staff">Staff</button>
        <button class="btn btn-default btn-xs rw-base" data-base="scepter">Scepter</button>
        <button class="btn btn-default btn-xs rw-base" data-base="wand">Wand</button>
        <button class="btn btn-default btn-xs rw-base" data-base="claw">Claw</button>
    </div>

    <!-- Runeword table -->
    <div class="panel panel-default">
        <table class="table table-hover" id="rwtable" style="margin-bottom:0">
            <thead>
                <tr>
                    <th style="width:180px">Runeword</th>
                    <th>Base</th>
                    <th style="width:60px">Sockets</th>
                    <th>Runes Required</th>
                    <th style="width:<?php echo array_key_exists($currUser, $authorized) ? '230px' : '110px'; ?>">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($runewords as $rw):
                $canMakeRow = $rw['missing'] === 0;
                $classids   = array();
                foreach ($rw['runes'] as $rune) {
                    if (isset($runeClassid[$rune])) $classids[] = $runeClassid[$rune];
                }
            ?>
            <tr data-filter="<?php echo $canMakeRow ? 'can' : 'cant'; ?>" data-base="<?php echo htmlspecialchars(strtolower($rw['base'])); ?>">
                <td><strong class="color8" style="font-family:Exocet,Arial;font-size:14px"><?php echo htmlspecialchars($rw['name']); ?></strong></td>
                <td style="font-size:12px;color:#888"><?php echo htmlspecialchars($rw['base']); ?></td>
                <td style="text-align:center"><?php echo $rw['sockets']; ?></td>
                <td>
                    <?php foreach ($rw['runes'] as $i => $rune): ?>
                    <span class="rune-badge <?php echo $rw['slots'][$i] ? 'rune-ok' : 'rune-miss'; ?>">
                        <?php echo htmlspecialchars($rune); ?>
                    </span>
                    <?php endforeach; ?>
                </td>
                <td style="white-space:nowrap">
                    <?php if ($canMakeRow): ?>
                        <span class="label label-success" style="font-size:11px">CAN MAKE <?php echo $rw['craftable']; ?></span>
                        <form method="post" action="runeshow.php" style="display:inline-block;margin:0;white-space:nowrap;vertical-align:middle">
                            <input type="hidden" name="realm"  value="<?php echo $queryR; ?>">
                            <input type="hidden" name="hc"     value="<?php echo $queryHC; ?>">
                            <input type="hidden" name="ladder" value="<?php echo $queryLD; ?>">
                            <input type="hidden" name="exp"    value="<?php echo $queryEXP; ?>">
                            <input type="hidden" name="runes"  value="<?php echo htmlspecialchars(implode(',', $rw['runes'])); ?>">
                            <button type="submit" class="btn btn-xs btn-warning" style="margin-left:6px;font-family:Exocet,Arial;font-size:11px">SHOW</button><input type="number" name="qty" value="1" min="1" max="99" style="width:42px;display:inline-block;margin-left:4px;padding:1px 3px;font-size:11px;height:22px;vertical-align:middle">
                        </form>
                    <?php else: ?>
                        <span class="label label-danger" style="font-size:11px">-<?php echo $rw['missing']; ?> rune<?php echo $rw['missing'] > 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="panel panel-default text-center">
            <div class="panel-footer">ItemManager 2026 &copy; dzik</div>
        </div>
    </div>
</div>

<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
<script>
var baseKeywords = {
    'armor':   ['armor'],
    'helm':    ['helm'],
    'shield':  ['shield'],
    'sword':   ['sword'],
    'axe':     ['axe'],
    'mace':    ['mace', 'hammer', 'club'],
    'polearm': ['polearm', 'spear'],
    'missile': ['missile'],
    'staff':   ['staff'],
    'scepter': ['scepter'],
    'wand':    ['wand'],
    'claw':    ['claw']
};
var activeStatus = 'all';
var activeBase   = 'all';

function applyRwFilter() {
    $('#rwtable tbody tr').each(function() {
        var statusOk = (activeStatus === 'all') || ($(this).data('filter') === activeStatus);
        var base     = $(this).attr('data-base') || '';
        var baseOk   = (activeBase === 'all');
        if (!baseOk && baseKeywords[activeBase]) {
            var kws = baseKeywords[activeBase];
            for (var i = 0; i < kws.length; i++) {
                if (base.indexOf(kws[i]) !== -1) { baseOk = true; break; }
            }
        }
        $(this).toggle(statusOk && baseOk);
    });
}

$('.rw-filter').click(function() {
    $('.rw-filter').removeClass('active');
    $(this).addClass('active');
    activeStatus = $(this).data('filter');
    applyRwFilter();
});

$('.rw-base').click(function() {
    $('.rw-base').removeClass('active');
    $(this).addClass('active');
    activeBase = $(this).data('base');
    applyRwFilter();
});
$("a.themeselect").click(function(e) {
    e.preventDefault();
    var theme = $(this).attr('theme');
    $.post('theme.php', {theme: theme}, function() {
        $("head link#layout1").attr("href", "themes/" + theme + "/css/bootstrap.css");
        $("head link#layout2").attr("href", "themes/" + theme + "/css/itemManager.css");
    });
});
</script>
</body>
</html>
