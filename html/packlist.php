<?php
error_reporting(0);
ob_start();
require 'config.php';
ob_clean();

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $admin) {
    http_response_code(403);
    exit;
}

$baseDir = realpath(dirname(__FILE__) . '/savedlist');

function safePath($base, $rel) {
    $rel = str_replace('\\', '/', trim($rel, '/\\'));
    if (empty($rel) || strpos($rel, '..') !== false || strpos($rel, ':') !== false) {
        return false;
    }
    return $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
}

function buildTree($dir, $base) {
    $items = array();
    if (!is_dir($dir)) return $items;
    $entries = scandir($dir);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $entry;
        $rel  = str_replace('\\', '/', ltrim(str_replace($base, '', $path), '/\\'));
        if (is_dir($path)) {
            $items[] = array('type' => 'dir', 'name' => $entry, 'path' => $rel, 'children' => buildTree($path, $base));
        } elseif (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'txt') {
            $items[] = array('type' => 'file', 'name' => pathinfo($entry, PATHINFO_FILENAME), 'path' => $rel);
        }
    }
    return $items;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'editor');

if ($action === 'list') {
    header('Content-Type: application/json');
    echo json_encode(buildTree($baseDir, $baseDir));
    exit;
}

if ($action === 'load') {
    header('Content-Type: application/json');
    $path = safePath($baseDir, isset($_GET['file']) ? $_GET['file'] : '');
    if (!$path || !file_exists($path) || is_dir($path)) { echo json_encode(array()); exit; }
    $lines  = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $result = array();
    foreach ($lines as $line) {
        $d = json_decode(trim($line), true);
        if ($d) $result[] = $d;
    }
    echo json_encode($result);
    exit;
}

if ($action === 'save') {
    header('Content-Type: application/json');
    $path    = safePath($baseDir, isset($_POST['file']) ? $_POST['file'] : '');
    $entries = json_decode(isset($_POST['data']) ? $_POST['data'] : '[]', true);
    if (!$path || !is_array($entries)) { echo json_encode(array('ok' => false, 'error' => 'Invalid')); exit; }
    $content = '';
    foreach ($entries as $e) {
        $kw = isset($e['keyword']) ? trim($e['keyword']) : '';
        if ($kw !== '') $content .= json_encode(array('keyword' => $kw, 'occu' => $e['occu'])) . "\n";
    }
    echo file_put_contents($path, $content) !== false
        ? json_encode(array('ok' => true))
        : json_encode(array('ok' => false, 'error' => 'Write failed'));
    exit;
}

if ($action === 'newfile') {
    header('Content-Type: application/json');
    $path = safePath($baseDir, isset($_POST['file']) ? $_POST['file'] : '');
    if (!$path) { echo json_encode(array('ok' => false, 'error' => 'Invalid path')); exit; }
    if (file_exists($path)) { echo json_encode(array('ok' => false, 'error' => 'Already exists')); exit; }
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    echo file_put_contents($path, '') !== false
        ? json_encode(array('ok' => true))
        : json_encode(array('ok' => false, 'error' => 'Create failed'));
    exit;
}

if ($action === 'newdir') {
    header('Content-Type: application/json');
    $path = safePath($baseDir, isset($_POST['dir']) ? $_POST['dir'] : '');
    if (!$path) { echo json_encode(array('ok' => false, 'error' => 'Invalid path')); exit; }
    if (is_dir($path)) { echo json_encode(array('ok' => false, 'error' => 'Already exists')); exit; }
    echo mkdir($path, 0755, true)
        ? json_encode(array('ok' => true))
        : json_encode(array('ok' => false, 'error' => 'Create failed'));
    exit;
}

if ($action === 'deletefile') {
    header('Content-Type: application/json');
    $path = safePath($baseDir, isset($_POST['file']) ? $_POST['file'] : '');
    if (!$path || !file_exists($path) || is_dir($path)) { echo json_encode(array('ok' => false, 'error' => 'Not found')); exit; }
    echo unlink($path)
        ? json_encode(array('ok' => true))
        : json_encode(array('ok' => false, 'error' => 'Delete failed'));
    exit;
}

if ($action === 'clonefile') {
    header('Content-Type: application/json');
    $src = safePath($baseDir, isset($_POST['file'])    ? $_POST['file']    : '');
    $dst = safePath($baseDir, isset($_POST['newfile']) ? $_POST['newfile'] : '');
    if (!$src || !file_exists($src) || is_dir($src)) { echo json_encode(array('ok' => false, 'error' => 'Source not found')); exit; }
    if (!$dst) { echo json_encode(array('ok' => false, 'error' => 'Invalid path')); exit; }
    if (file_exists($dst)) { echo json_encode(array('ok' => false, 'error' => 'Already exists')); exit; }
    $dir = dirname($dst);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    echo copy($src, $dst)
        ? json_encode(array('ok' => true))
        : json_encode(array('ok' => false, 'error' => 'Copy failed'));
    exit;
}

if ($action === 'renamefile') {
    header('Content-Type: application/json');
    $src = safePath($baseDir, isset($_POST['file'])    ? $_POST['file']    : '');
    $dst = safePath($baseDir, isset($_POST['newfile']) ? $_POST['newfile'] : '');
    if (!$src || !file_exists($src) || is_dir($src)) { echo json_encode(array('ok' => false, 'error' => 'Not found')); exit; }
    if (!$dst) { echo json_encode(array('ok' => false, 'error' => 'Invalid path')); exit; }
    if (file_exists($dst)) { echo json_encode(array('ok' => false, 'error' => 'Already exists')); exit; }
    $dir = dirname($dst);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (rename($src, $dst)) {
        $rel = str_replace('\\', '/', str_replace($baseDir . DIRECTORY_SEPARATOR, '', $dst));
        echo json_encode(array('ok' => true, 'newfile' => $rel));
    } else {
        echo json_encode(array('ok' => false, 'error' => 'Rename failed'));
    }
    exit;
}

// Default: editor UI (injected into #output)
?>
<div class="panel-heading">
    <h1 class="panel-title">PACKLIST EDITOR
        <span class="pull-right" style="margin-top:-2px;">
            <button class="btn btn-xs btn-default" id="pl-new-folder">+ Folder</button>
            <button class="btn btn-xs btn-default" id="pl-new-file" style="margin-left:4px;">+ File</button>
            <button class="btn btn-xs btn-default" id="pl-clone-file" style="margin-left:8px; display:none;">Clone</button>
            <button class="btn btn-xs btn-default" id="pl-rename-file" style="margin-left:4px; display:none;">Rename</button>
            <button class="btn btn-xs btn-danger" id="pl-delete-file" style="margin-left:4px; display:none;">Delete</button>
        </span>
    </h1>
</div>
<div style="display:flex; min-height:460px;">
    <!-- Tree -->
    <div id="pl-tree-panel" style="width:220px; flex-shrink:0; border-right:1px solid #ccc; padding:10px 8px; overflow-y:auto; font-size:13px;">
        Loading...
    </div>
    <!-- Editor -->
    <div style="flex:1; padding:10px 14px; overflow:auto;">
        <div id="pl-editor-header" style="display:none; margin-bottom:8px;">
            <span class="exocet" id="pl-current-file" style="font-size:12px; color:#888;"></span>
            <span class="pull-right">
                <button class="btn btn-xs btn-default" id="pl-add-entry">+ Entry</button>
                <button class="btn btn-xs btn-success" id="pl-save" style="margin-left:4px;">Save</button>
            </span>
        </div>
        <div id="pl-editor-body">
            <p class="text-muted" style="padding:30px 10px;">Select a file from the tree on the left.</p>
        </div>
    </div>
</div>
<script>
(function($) {
    var currentFile = null;

    function esc(s) { return $('<div>').text(String(s == null ? '' : s)).html(); }

    /* ---- Tree ---- */
    function loadTree() {
        $.get('packlist.php?action=list', function(raw) {
            var data = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            $('#pl-tree-panel').html(renderTree(data, 0));
        }).fail(function() {
            $('#pl-tree-panel').html('<span class="text-danger">Load failed.</span>');
        });
    }

    function renderTree(nodes, depth) {
        if (!nodes || !nodes.length) {
            return depth === 0 ? '<span style="color:#999;">(empty)</span>' : '';
        }
        var ul = '<ul style="list-style:none;padding-left:' + (depth ? 12 : 0) + 'px;margin:0;">';
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            if (n.type === 'dir') {
                ul += '<li style="margin:1px 0;">';
                ul += '<span class="pl-dir pl-drop-target" data-path="' + esc(n.path) + '" style="cursor:pointer;display:block;padding:2px 4px;border-radius:3px;">';
                ul += '<span class="pl-arrow" style="display:inline-block;width:12px;">&#9654;</span>';
                ul += ' <strong>' + esc(n.name) + '</strong></span>';
                ul += '<div class="pl-children" style="display:none;">' + renderTree(n.children, depth + 1) + '</div>';
                ul += '</li>';
            } else {
                ul += '<li style="margin:1px 0;">';
                ul += '<a class="pl-file" href="#" draggable="true" data-path="' + esc(n.path) + '" style="display:block;padding:2px 4px 2px 16px;border-radius:3px;font-size:12px;">' + esc(n.name) + '</a>';
                ul += '</li>';
            }
        }
        return ul + '</ul>';
    }

    $('#pl-tree-panel').on('click', '.pl-dir', function(e) {
        e.stopPropagation();
        var $ch = $(this).next('.pl-children');
        $ch.toggle();
        $(this).find('.pl-arrow').html($ch.is(':visible') ? '&#9660;' : '&#9654;');
    });

    $('#pl-tree-panel').on('click', '.pl-file', function(e) {
        e.preventDefault();
        $('#pl-tree-panel .pl-file').css('background','');
        $(this).css('background','#d9edf7');
        currentFile = $(this).attr('data-path');
        $('#pl-clone-file, #pl-rename-file, #pl-delete-file').show();
        loadFile(currentFile);
    });

    /* ---- File editor ---- */
    function loadFile(path) {
        $('#pl-editor-header').hide();
        $('#pl-editor-body').html('<p class="text-muted" style="padding:10px;">Loading...</p>');
        $.get('packlist.php?action=load&file=' + encodeURIComponent(path), function(raw) {
            var entries = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            $('#pl-current-file').text(path);
            $('#pl-editor-header').show();
            renderEntries(entries);
        }).fail(function() {
            $('#pl-editor-body').html('<span class="text-danger">Failed to load file.</span>');
        });
    }

    function renderEntries(entries) {
        var html = '<table class="table table-condensed table-hover" style="font-size:12px;margin:0;">';
        html += '<thead><tr>'
              + '<th style="width:28px;">#</th>'
              + '<th>Keyword(s) <span style="font-weight:normal;color:#999;font-size:11px;">(comma-sep · prefix ! to exclude)</span></th>'
              + '<th style="width:65px;">Qty</th>'
              + '<th style="width:28px;"></th>'
              + '</tr></thead><tbody id="pl-entries">';
        for (var i = 0; i < entries.length; i++) {
            var kw  = entries[i].keyword !== undefined ? entries[i].keyword : (entries[i].name || '');
            var qty = entries[i].occu    !== undefined ? entries[i].occu    : (entries[i].qty  || '1');
            html += entryRow(i + 1, kw, qty);
        }
        html += '</tbody></table>';
        $('#pl-editor-body').html(html);
    }

    function entryRow(num, kw, qty) {
        return '<tr>'
             + '<td style="color:#999;vertical-align:middle;">' + num + '</td>'
             + '<td><input class="form-control input-sm pl-kw" value="' + esc(kw) + '"></td>'
             + '<td><input class="form-control input-sm pl-qty" type="number" min="1" value="' + esc(qty) + '"></td>'
             + '<td><button class="btn btn-xs btn-danger pl-del-row" title="Remove">&#x2715;</button></td>'
             + '</tr>';
    }

    $('#pl-editor-body').on('click', '.pl-del-row', function() {
        $(this).closest('tr').remove();
        renumber();
    });

    function renumber() {
        $('#pl-entries tr').each(function(i) { $(this).find('td:first').text(i + 1); });
    }

    function collectEntries() {
        var entries = [];
        $('#pl-entries tr').each(function() {
            var kw = $(this).find('.pl-kw').val().trim();
            var qty = $(this).find('.pl-qty').val() || '1';
            if (kw) entries.push({keyword: kw, occu: qty});
        });
        return entries;
    }

    $('#pl-add-entry').click(function() {
        var count = $('#pl-entries tr').length;
        $('#pl-entries').append(entryRow(count + 1, '', '1'));
        $('#pl-entries tr:last .pl-kw').focus();
    });

    $('#pl-save').click(function() {
        if (!currentFile) return;
        var $btn = $(this);
        $.post('packlist.php', {
            action: 'save',
            file: currentFile,
            data: JSON.stringify(collectEntries())
        }, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            if (r && r.ok) {
                $btn.text('Saved!');
                setTimeout(function() { $btn.text('Save'); }, 1500);
            } else {
                alert('Save error: ' + (r ? r.error : 'unknown'));
            }
        });
    });

    /* ---- New file / folder / delete ---- */
    $('#pl-new-file').click(function() {
        var name = prompt('File name (no .txt):');
        if (!name || !name.trim()) return;
        var folder = prompt('Folder path (blank = root, e.g. Craft/Blood):');
        var path = (folder && folder.trim() ? folder.trim().replace(/\\/g,'/').replace(/\/+$/,'') + '/' : '') + name.trim() + '.txt';
        $.post('packlist.php', {action: 'newfile', file: path}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            r && r.ok ? loadTree() : alert('Error: ' + (r ? r.error : 'unknown'));
        });
    });

    $('#pl-new-folder').click(function() {
        var path = prompt('Folder path (e.g. Runeword/Ladder or just NewFolder):');
        if (!path || !path.trim()) return;
        $.post('packlist.php', {action: 'newdir', dir: path.trim()}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            r && r.ok ? loadTree() : alert('Error: ' + (r ? r.error : 'unknown'));
        });
    });

    $('#pl-delete-file').click(function() {
        if (!currentFile || !confirm('Delete "' + currentFile + '"?\nThis cannot be undone.')) return;
        $.post('packlist.php', {action: 'deletefile', file: currentFile}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            if (r && r.ok) {
                currentFile = null;
                $('#pl-clone-file, #pl-rename-file, #pl-delete-file').hide();
                $('#pl-editor-header').hide();
                $('#pl-editor-body').html('<p class="text-muted" style="padding:30px 10px;">File deleted.</p>');
                loadTree();
            } else { alert('Error: ' + (r ? r.error : 'unknown')); }
        });
    });

    /* ---- Clone ---- */
    $('#pl-clone-file').click(function() {
        if (!currentFile) return;
        var parts = currentFile.split('/');
        var oldName = parts[parts.length - 1].replace(/\.txt$/i, '');
        var folder  = parts.slice(0, -1).join('/');
        var newName = prompt('Clone name (no .txt):', oldName + ' copy');
        if (!newName || !newName.trim()) return;
        var newPath = (folder ? folder + '/' : '') + newName.trim() + '.txt';
        $.post('packlist.php', {action: 'clonefile', file: currentFile, newfile: newPath}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            r && r.ok ? loadTree() : alert('Error: ' + (r ? r.error : 'unknown'));
        });
    });

    /* ---- Rename / Move ---- */
    $('#pl-rename-file').click(function() {
        if (!currentFile) return;
        var newPath = prompt('New path (e.g. Runeword/MyFile or just MyFile):', currentFile.replace(/\.txt$/i, ''));
        if (!newPath || !newPath.trim()) return;
        newPath = newPath.trim().replace(/\.txt$/i, '') + '.txt';
        $.post('packlist.php', {action: 'renamefile', file: currentFile, newfile: newPath}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            if (r && r.ok) {
                currentFile = r.newfile;
                $('#pl-current-file').text(currentFile);
                loadTree();
            } else { alert('Error: ' + (r ? r.error : 'unknown')); }
        });
    });

    /* ---- Drag-and-drop move ---- */
    var draggedFile = null;

    $('#pl-tree-panel').on('dragstart', '.pl-file', function(e) {
        draggedFile = $(this).attr('data-path');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/plain', draggedFile);
    });

    $('#pl-tree-panel').on('dragover', '.pl-drop-target, #pl-tree-panel', function(e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        $(this).css('outline', '2px solid #5bc0de');
    }).on('dragleave', '.pl-drop-target, #pl-tree-panel', function() {
        $(this).css('outline', '');
    }).on('drop', '.pl-drop-target', function(e) {
        e.preventDefault();
        $(this).css('outline', '');
        if (!draggedFile) return;
        var folder   = $(this).attr('data-path');
        var fileName = draggedFile.split('/').pop();
        var newPath  = folder + '/' + fileName;
        if (newPath === draggedFile) return;
        $.post('packlist.php', {action: 'renamefile', file: draggedFile, newfile: newPath}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            if (r && r.ok) {
                if (draggedFile === currentFile) { currentFile = r.newfile; $('#pl-current-file').text(currentFile); }
                draggedFile = null;
                loadTree();
            } else { alert('Move failed: ' + (r ? r.error : 'unknown')); }
        });
    });

    // Drop onto tree panel root = move to root
    $('#pl-tree-panel').on('drop', function(e) {
        if ($(e.target).closest('.pl-drop-target').length) return;
        e.preventDefault();
        $(this).css('outline', '');
        if (!draggedFile) return;
        var fileName = draggedFile.split('/').pop();
        if (draggedFile === fileName) return;
        $.post('packlist.php', {action: 'renamefile', file: draggedFile, newfile: fileName}, function(raw) {
            var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
            if (r && r.ok) {
                if (draggedFile === currentFile) { currentFile = r.newfile; $('#pl-current-file').text(currentFile); }
                draggedFile = null;
                loadTree();
            } else { alert('Move failed: ' + (r ? r.error : 'unknown')); }
        });
    }).on('dragover', function(e) {
        if ($(e.target).closest('.pl-drop-target').length) return;
        e.preventDefault();
        $(this).css('outline', '2px dashed #aaa');
    }).on('dragleave', function(e) {
        if (!$(e.relatedTarget).closest('#pl-tree-panel').length) $(this).css('outline', '');
    });

    loadTree();

})(jQuery);
</script>
