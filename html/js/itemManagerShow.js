// Called on page ready (from index.php) and after every AJAX item load (from itemManager.js)
function initItemRows() {
    showHighligh();
    updateDropCount();

    // Direct binding — rows exist in DOM at this point
    $('#itemstable > tbody > tr').off('click.itemshow').on('click.itemshow', function () {
        var img = $('div.tooltipster-base');
        if (img.length > 0) {
            imgurUpload(img, true, $(this).attr('drImage') + "-" + $(this).attr('drmd5'));
        }

        if ($(this).hasClass("selecteditem") === false) {
            $(this).addClass("selecteditem");

            var data   = $(this).attr('drname');
            var rowid  = $(this).attr('dritemid');
            show.push(data);
            rowsid.push(rowid);

            var dropdata = {
                dropProfile: "",
                realm:    $(this).attr('drrealm'),
                account:  $(this).attr('draccount'),
                charName: $(this).attr('drchar'),
                itemType: $(this).attr('dritemtype'),
                dropit:   $(this).attr('drmd5'),
                skin:     $(this).attr('drImage'),
                itemID:   $(this).attr('drID')
            };
            droparray.push(JSON.stringify(dropdata));
            updateDropCount();

            var output = ParseSame(show);
            var di = document.getElementById('dropitem');
            var li = document.getElementById('listinfo');
            if (di) { di.value = JSON.stringify(droparray); }
            if (li) { li.value = JSON.stringify(droparray); }
            $("#droplist").html(output);
            $("#tradelist").html(output);

        } else {
            $(this).removeClass("selecteditem");

            var data   = $(this).attr('drname');
            var rowid  = $(this).attr('dritemid');
            var pushtodrop = JSON.stringify({
                dropProfile: "",
                realm:    $(this).attr('drrealm'),
                account:  $(this).attr('draccount'),
                charName: $(this).attr('drchar'),
                itemType: $(this).attr('dritemtype'),
                dropit:   $(this).attr('drmd5'),
                skin:     $(this).attr('drImage'),
                itemID:   $(this).attr('drID')
            });

            for (var i = droparray.length - 1; i >= 0; i--) {
                if (droparray[i] === pushtodrop) { droparray.splice(i, 1); break; }
            }
            for (var i = show.length - 1; i >= 0; i--) {
                if (show[i] === data) { show.splice(i, 1); break; }
            }
            for (var i = rowsid.length - 1; i >= 0; i--) {
                if (rowsid[i] === rowid) { rowsid.splice(i, 1); break; }
            }
            updateDropCount();

            var output = ParseSame(show);
            var di = document.getElementById('dropitem');
            var li = document.getElementById('listinfo');
            if (di) { di.value = JSON.stringify(droparray); }
            if (li) { li.value = JSON.stringify(droparray); }
            $("#droplist").html(output);
            $("#tradelist").html(output);
        }
    });

    // Hide/show equipped
    $('.showhide').off('click.showhide').on('click.showhide', function () {
        if ($(this).html() === "hide equiped") {
            $('.loc1').hide();
            $(this).html("show equiped");
        } else {
            $('.loc1').show();
            $(this).html("hide equiped");
        }
    });

    // Tooltips
    var tooltipSide = window.innerWidth <= 991 ? 'top' : 'left';
    $('.show-tooltip').each(function () {
        var p = $(this).parent();
        if (p.is('td')) {
            $(this).css('padding', p.css('padding'));
            p.css('padding', '0 0');
        }
        $(this).tooltipster({
            delay: 0, speed: 0, touchDevices: false, arrow: false,
            position: tooltipSide, interactive: true, interactiveTolerance: 30,
            contentAsHTML: true, animation: 'fade', trigger: 'hover'
        });
    });

    // Mobile: inject card images
    if (window.innerWidth <= 991) {
        $('#itemstable tbody tr.item').each(function () {
            var img = $(this).attr('drImage');
            if (img) {
                $(this).find('td:last').prepend(
                    '<img class="card-img" src="images/items/' + img + '.png" onerror="this.style.display=\'none\'">'
                );
            }
        });
    }

    $('table#itemstable').tablesorter();
}

;(function ($, window, document) {
    $(document).ready(function () {
        loadDropFromStorage();
        initItemRows();
    });
})( jQuery, window, document );

function showHighligh() {
    $('tr.item').each(function () {
        var checkrow = $(this).attr('dritemid');
        if (rowsid.indexOf(checkrow) > -1) {
            $(this).addClass("selecteditem");
        } else {
            $(this).removeClass("selecteditem");
        }
        if (hideid.indexOf(checkrow) > -1) {
            $(this).hide();
        }
    });
}

function ParseSame(list) {
    var result = {}, STR = "", i, j;
    for (i = 0; i < list.length; i++) {
        result[list[i]] = (result[list[i]] || 0) + 1;
    }
    for (j in result) {
        STR += j + " x" + result[j] + "<br>";
    }
    return '<div class="color8" style="text-align:center"><strong>' + list.length + ' item(s) selected.</strong></div><br />' + STR;
}

function MarkThem() {
    var counter = document.getElementById('massMark').value;
    if (counter < 1) { return false; }
    var marked = 0;
    $('tr.item').each(function () {
        if (!$(this).hasClass("selecteditem") && marked < counter) {
            $(this).addClass("selecteditem");
            var data   = $(this).attr('drname');
            var rowid  = $(this).attr('dritemid');
            show.push(data);
            rowsid.push(rowid);
            droparray.push(JSON.stringify({
                dropProfile: "",
                realm:    $(this).attr('drrealm'),
                account:  $(this).attr('draccount'),
                charName: $(this).attr('drchar'),
                itemType: $(this).attr('dritemtype'),
                dropit:   $(this).attr('drmd5'),
                skin:     $(this).attr('drImage'),
                itemID:   $(this).attr('drID')
            }));
            marked++;
        }
    });
    updateDropCount();
    var output = ParseSame(show);
    var di = document.getElementById('dropitem');
    if (di) { di.value = JSON.stringify(droparray); }
    $("#droplist").html(output);
    $("#tradelist").html('<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Cannot create trade list with mass mark function.</div>');
}

function ClearAll() {
    show = []; rowsid = []; hideid = []; droparray = [];
    var di = document.getElementById('dropitem');
    var li = document.getElementById('listinfo');
    if (di) { di.value = ""; }
    if (li) { li.value = ""; }
    $("#tradelist").html('<div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Cleared!</div>');
    $("#droplist").html('<div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Cleared!</div>');
    try { localStorage.removeItem('d2drop_array'); localStorage.removeItem('d2show_list'); localStorage.removeItem('d2rowsid'); } catch(e) {}
    updateDropCount();
    showHighligh();
}

function updateDropCount() {
    var n = droparray.length;
    var $btn = $('#drop-show-btn');
    if ($btn.length) {
        $('#drop-count').text(n);
        $btn.toggleClass('btn-danger', n > 0).toggleClass('btn-default', n === 0);
    }
    try {
        localStorage.setItem('d2drop_array', JSON.stringify(droparray));
        localStorage.setItem('d2show_list',  JSON.stringify(show));
        localStorage.setItem('d2rowsid',     JSON.stringify(rowsid));
    } catch(e) {}
}

function loadDropFromStorage() {
    try {
        var da = localStorage.getItem('d2drop_array');
        var sl = localStorage.getItem('d2show_list');
        var ri = localStorage.getItem('d2rowsid');
        if (da) { droparray = JSON.parse(da); }
        if (sl) { show      = JSON.parse(sl); }
        if (ri) { rowsid    = JSON.parse(ri); }
        if (droparray.length) {
            var di = document.getElementById('dropitem');
            var li = document.getElementById('listinfo');
            if (di) { di.value = JSON.stringify(droparray); }
            if (li) { li.value = JSON.stringify(droparray); }
            var output = ParseSame(show);
            if ($("#droplist").length)  { $("#droplist").html(output); }
            if ($("#tradelist").length) { $("#tradelist").html(output); }
            updateDropCount();
        }
    } catch(e) {}
}
