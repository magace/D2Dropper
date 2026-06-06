<!DOCTYPE html>
<html lang="en">
<?php
require 'functions.php';
require 'config.php';
require 'grail_data.php';
require 'nip_data.php';
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

// De-duplicate reference list by name+quality
$refUniq = array();
$refMap  = array(); // lowercase name => true
foreach ($grailItems as $item) {
    $key = strtolower($item['name']).'|'.$item['quality'];
    if (!isset($refMap[$key])) {
        $refMap[$key]  = true;
        $refUniq[]     = $item;
    }
}
$grailItems = $refUniq;

// Normalize name for matching: lowercase, trim, unify apostrophe variants
function grailNorm($s) {
    $s = str_replace("\xe2\x80\x99", "'", $s); // right single quote → straight
    $s = str_replace("\xe2\x80\x98", "'", $s); // left  single quote → straight
    return strtolower(trim($s));
}

// Query what the user actually has (unique + set, no classid filter so NULL classids are included)
$found       = array(); // normed name|quality => count
$dbSample    = array(); // first 30 rows for diagnosis
$dbRawTotal  = 0;       // set inside the try block
try {
    $conn = new PDO('sqlite:ItemDB.s3db');
    $sql  = 'SELECT itemName, itemQuality, COUNT(*) as cnt
             FROM muleItems
             LEFT JOIN muleChars ON itemCharId = charId
             LEFT JOIN muleAccounts ON charAccountId = accountId
             WHERE accountRealm = '.$queryR.'
             AND charHardcore = '.$queryHC.'
             AND charLadder = '.$queryLD.'
             AND charExpansion = '.$queryEXP.'
             AND itemQuality IN (5,7)
             GROUP BY itemName, itemQuality
             ORDER BY itemQuality DESC, itemName ASC';
    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;
    $dbRawTotal = count($rows);
    $dbRawTotal = count($rows);
    foreach ($rows as $idx => $row) {
        $dbNorm = grailNorm($row['itemName']);
        $dbQ    = intval($row['itemQuality']);
        $cnt    = intval($row['cnt']);
        // DB stores "Unique Name Base Type" — match reference by starts-with
        foreach ($grailItems as $refItem) {
            if ($refItem['quality'] === $dbQ) {
                $refNorm = grailNorm($refItem['name']);
                if ($dbNorm === $refNorm || strpos($dbNorm, $refNorm . ' ') === 0) {
                    $key = $refNorm.'|'.$dbQ;
                    $found[$key] = isset($found[$key]) ? $found[$key] + $cnt : $cnt;
                    break;
                }
            }
        }
        if ($idx < 30) { $dbSample[] = $row; }
    }
} catch (PDOException $e) { $conn = null; }

// Match reference against found
$totalRef   = count($grailItems);
$foundCount = 0;
$catData    = array(); // cat => array of items with status

foreach ($grailItems as $item) {
    $key    = grailNorm($item['name']).'|'.$item['quality'];
    $cnt    = isset($found[$key]) ? $found[$key] : 0;
    $have   = $cnt > 0;
    if ($have) $foundCount++;
    $catData[$item['cat']][] = array(
        'name'    => $item['name'],
        'quality' => $item['quality'],
        'cnt'     => $cnt,
        'have'    => $have,
    );
}

ksort($catData);

$pct        = $totalRef > 0 ? round($foundCount / $totalRef * 100, 1) : 0;
$uniqueRef  = 0; $uniqueFound  = 0;
$setRef     = 0; $setFound     = 0;
foreach ($grailItems as $item) {
    $key = strtolower($item['name']).'|'.$item['quality'];
    $h   = isset($found[$key]) && $found[$key] > 0;
    if ($item['quality'] === 7) { $uniqueRef++; if ($h) $uniqueFound++; }
    else                        { $setRef++;    if ($h) $setFound++;    }
}
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="images/icons/Akara.ico">
    <title>Grail Tracker</title>
    <link id="layout1" rel="stylesheet" href="themes/<?php echo $themeName; ?>/css/bootstrap.css">
    <link id="layout2" rel="stylesheet" type="text/css" href="themes/<?php echo $themeName; ?>/css/itemManager.css">
    <style>
        .grail-item { display:inline-block; margin:3px 4px; padding:3px 8px; border-radius:3px; font-size:12px; font-family:Arial; cursor:default; }
        .grail-have { background:#1a3a1a; color:#18FC00; border:1px solid #285228; }
        .grail-miss { background:#1a1a1a; color:#555; border:1px solid #333; cursor:pointer; }
        .grail-miss:hover { border-color:#666; color:#888; }
        .grail-miss-u { color:#666; }
        .grail-copied { background:#1a1a4a !important; color:#aaaaff !important; border-color:#4444aa !important; }
        .cat-panel  { margin-bottom:14px; }
        .progress   { margin-bottom:4px; }
        .stat-box   { display:inline-block; text-align:center; padding:10px 20px; margin:4px; border:1px solid #444; border-radius:4px; min-width:120px; }
        .stat-num   { font-size:28px; font-family:Exocet,Arial; }
        .filter-bar { margin-bottom:12px; }
        #nip-toast  { display:none; position:fixed; bottom:30px; left:50%; transform:translateX(-50%); background:#222; color:#aaaaff; border:1px solid #4444aa; border-radius:4px; padding:7px 18px; font-size:13px; font-family:monospace; z-index:9999; white-space:nowrap; }
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
                <li><a class="exocet" href="runeword.php?realm=<?php echo $queryR; ?>&hc=<?php echo $queryHC; ?>&ladder=<?php echo $queryLD; ?>&exp=<?php echo $queryEXP; ?>">RUNEWORDS</a></li>
                <li><a class="exocet" href="index.php?realm=<?php echo $queryR; ?>&hc=<?php echo $queryHC; ?>&ladder=<?php echo $queryLD; ?>&exp=<?php echo $queryEXP; ?>">BACK</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">

    <!-- Summary -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">HOLY GRAIL &mdash; <?php echo htmlspecialchars($realmLabel); ?></h3>
        </div>
        <div class="panel-body" style="padding:12px">
            <div class="progress" style="height:28px;margin-bottom:10px">
                <div class="progress-bar progress-bar-warning progress-bar-striped"
                     style="width:<?php echo $pct; ?>%;line-height:28px;font-size:14px;font-family:Exocet,Arial">
                    <?php echo $pct; ?>%
                </div>
            </div>
            <div style="text-align:center">
                <div class="stat-box">
                    <div class="stat-num color8"><?php echo $foundCount; ?> / <?php echo $totalRef; ?></div>
                    <div style="font-size:11px;color:#888">TOTAL FOUND</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num color4"><?php echo $uniqueFound; ?> / <?php echo $uniqueRef; ?></div>
                    <div style="font-size:11px;color:#888">UNIQUES</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num color2"><?php echo $setFound; ?> / <?php echo $setRef; ?></div>
                    <div style="font-size:11px;color:#888">SET ITEMS</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num color1"><?php echo $totalRef - $foundCount; ?></div>
                    <div style="font-size:11px;color:#888">MISSING</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnostic panel (shown when 0 found or DB has items not matching reference) -->
    <?php if ($foundCount === 0 || $dbRawTotal === 0): ?>
    <div class="panel panel-warning">
        <div class="panel-heading" style="cursor:pointer" data-toggle="collapse" data-target="#grail-diag">
            <strong>&#9888; Diagnostic — <?php echo $dbRawTotal; ?> unique/set items in DB, <?php echo $foundCount; ?> matched reference list. Click to expand.</strong>
        </div>
        <div id="grail-diag" class="panel-body collapse" style="font-family:monospace;font-size:12px">
            <p>First <?php echo count($dbSample); ?> item names as stored in DB (itemQuality 5=set, 7=unique):</p>
            <table class="table table-condensed" style="font-size:11px">
                <tr><th>itemName (raw)</th><th>Quality</th><th>Count</th></tr>
                <?php foreach ($dbSample as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['itemName']); ?></td>
                    <td><?php echo $s['itemQuality']; ?></td>
                    <td><?php echo $s['cnt']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($dbRawTotal === 0): ?>
                <tr><td colspan="3" style="color:red">No quality 5 or 7 items found for this realm/HC/ladder/exp combination.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="filter-bar">
        <button class="btn btn-default active grail-filter" data-filter="all">All</button>
        <button class="btn btn-success grail-filter" data-filter="have">Found</button>
        <button class="btn btn-danger grail-filter" data-filter="miss">Missing</button>
        <button class="btn btn-default grail-filter" data-filter="unique" style="margin-left:10px">Uniques Only</button>
        <button class="btn btn-default grail-filter" data-filter="set">Sets Only</button>
    </div>

    <!-- Items by category -->
    <?php foreach ($catData as $cat => $items):
        $catHave = 0; foreach ($items as $i) { if ($i['have']) $catHave++; }
    ?>
    <div class="panel panel-default cat-panel" data-cat="<?php echo htmlspecialchars($cat); ?>">
        <div class="panel-heading" style="padding:6px 12px">
            <strong class="exocet" style="font-size:13px"><?php echo htmlspecialchars($cat); ?></strong>
            <span style="float:right;font-size:12px;color:#888"><?php echo $catHave; ?> / <?php echo count($items); ?></span>
        </div>
        <div class="panel-body" style="padding:8px 10px">
            <?php foreach ($items as $item):
                $q = $item['quality'] === 7 ? 'unique' : 'set';
                $colorClass = $item['quality'] === 7 ? 'color4' : 'color2';
                $haveClass  = $item['have'] ? 'grail-have' : 'grail-miss';
                $nipQuality = $item['quality'] === 7 ? 'Unique' : 'Set';
                $nipKey     = strtolower($item['name']);
                if (isset($nipFull[$nipKey])) {
                    $nipLine = $nipFull[$nipKey].' // '.$item['name'];
                } elseif (isset($nipBase[$nipKey])) {
                    $nipLine = '[Name] == '.$nipBase[$nipKey].' && [Flag] != Identified && [Quality] == '.$nipQuality.' // '.$item['name'];
                } else {
                    $nipLine = '[Name] == ??? && [Flag] != Identified && [Quality] == '.$nipQuality.' // '.$item['name'];
                }
                $title      = $item['have'] ? 'Have: '.$item['cnt'].' — click to show' : 'Click to copy NIP line';
            ?>
            <?php if ($item['have']): ?>
            <a href="grailshow.php?name=<?php echo urlencode($item['name']); ?>&cnt=<?php echo $item['cnt']; ?>&realm=<?php echo $queryR; ?>&hc=<?php echo $queryHC; ?>&ladder=<?php echo $queryLD; ?>&exp=<?php echo $queryEXP; ?>" style="text-decoration:none">
            <?php endif; ?>
            <span class="grail-item <?php echo $haveClass; ?> grail-q-<?php echo $q; ?>"
                  data-have="<?php echo $item['have'] ? 'have' : 'miss'; ?>"
                  data-quality="<?php echo $q; ?>"
                  <?php if (!$item['have']): ?>
                  data-nipline="<?php echo htmlspecialchars($nipLine, ENT_QUOTES); ?>"
                  <?php endif; ?>
                  title="<?php echo htmlspecialchars($title); ?>">
                <?php if ($item['have']): ?>
                    <span class="<?php echo $colorClass; ?>"><?php echo htmlspecialchars($item['name']); ?></span>
                    <?php if ($item['cnt'] > 1): ?>
                        <span style="color:#aaa;font-size:10px"> x<?php echo $item['cnt']; ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="grail-miss-u"><?php echo htmlspecialchars($item['name']); ?></span>
                <?php endif; ?>
            </span>
            <?php if ($item['have']): ?></a><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="row">
        <div class="panel panel-default text-center">
            <div class="panel-footer">ItemManager 2026 &copy; dzik</div>
        </div>
    </div>
</div>

<div id="nip-toast"></div>

<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
<script>
var nipToastTimer = null;
function showNipToast(text) {
    clearTimeout(nipToastTimer);
    $('#nip-toast').text(text).stop(true,true).fadeIn(150);
    nipToastTimer = setTimeout(function() { $('#nip-toast').fadeOut(400); }, 2200);
}

$(document).on('click', '.grail-miss', function() {
    var nipLine = $(this).attr('data-nipline');
    if (!nipLine) return;
    var $el = $(this);
    function flashCopied() {
        $el.addClass('grail-copied');
        showNipToast('Copied: ' + nipLine);
        setTimeout(function() { $el.removeClass('grail-copied'); }, 1200);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(nipLine).then(flashCopied, function() {
            fallbackCopy(nipLine, flashCopied);
        });
    } else {
        fallbackCopy(nipLine, flashCopied);
    }
});

function fallbackCopy(text, cb) {
    var ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
    if (cb) cb();
}

var fHave    = null; // null | 'have' | 'miss'
var fQuality = null; // null | 'unique' | 'set'

function applyGrailFilter() {
    var $items = $('.grail-item').show();
    if (fHave    === 'have')   $items.filter('.grail-miss').hide();
    if (fHave    === 'miss')   $items.filter('.grail-have').hide();
    if (fQuality === 'unique') $items.filter('.grail-q-set').hide();
    if (fQuality === 'set')    $items.filter('.grail-q-unique').hide();
    // Show all panels first so :visible works correctly inside them, then hide empty ones
    $('.cat-panel').show().each(function() {
        if ($(this).find('.grail-item:visible').length === 0) $(this).hide();
    });
}

$('.grail-filter').click(function() {
    var f = $(this).data('filter');
    if (f === 'all') {
        fHave = null; fQuality = null;
        $('.grail-filter').removeClass('active');
        $(this).addClass('active');
    } else if (f === 'have' || f === 'miss') {
        fHave = (fHave === f) ? null : f;
        $('.grail-filter[data-filter="have"],.grail-filter[data-filter="miss"]').removeClass('active');
        if (fHave) $(this).addClass('active');
        $('.grail-filter[data-filter="all"]').toggleClass('active', !fHave && !fQuality);
    } else if (f === 'unique' || f === 'set') {
        fQuality = (fQuality === f) ? null : f;
        $('.grail-filter[data-filter="unique"],.grail-filter[data-filter="set"]').removeClass('active');
        if (fQuality) $(this).addClass('active');
        $('.grail-filter[data-filter="all"]').toggleClass('active', !fHave && !fQuality);
    }
    applyGrailFilter();
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
