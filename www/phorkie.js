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
