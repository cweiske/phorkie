function filenameChange(elem, id) {
    var filename = elem.value;
    var hasExt = filename.indexOf(".") != -1;
    if (hasExt) {
        $('#typeselect_' + id).hide();
        $('#typetext_' + id).show();
    } else {
        $('#typeselect_' + id).show();
        $('#typetext_' + id).hide();
    }
}

function initEdit()
{
    initFilenames();
    initAdditionals();
    $('.filegroup:visible:last textarea').focus();
}
function initFilenames()
{
    $('input.filename').each(
        function(num, elem) {
            var id = elem.id;
            var pos = id.indexOf('_');
            if (pos != -1) {
                var elemNum = id.substr(pos + 1);
                if (elemNum != 'new') {
                    filenameChange(elem, elemNum);
                }
            }
        }
    );
}
function initAdditionals()
{
    $('a.additional-btn').each(
        function(num, elem) {
            toggleAdditional(elem, 0);
            $(elem).show();
        }
    );
}

function toggleAdditional(elem, time)
{
    if (undefined == time) {
        time = 'fast';
    }
    var jt = jQuery(elem);
    jt.children('i').toggleClass('icon-chevron-down')
        .toggleClass('icon-chevron-up');
    jt.parents('.row-fluid').children('.additional').slideToggle(time);
    //jt.parents('.row-fluid').children('.additional').animate(time);
}
