jQuery(function($) {
    document.getElementsByTagName("body")[0]
        .insertAdjacentHTML(
            'beforeend',
            '<iframe id="autologin" src="login.php?autologin=1"'
            + ' width="10" height="10" style="display: none"'
            + '></iframe>'
        );
    /*;
    $.ajax("../login.php?autologin=1")
        .done(function() {
            alert("success");
        })
        .fail(function() {
            alert("error");
        });
    */
});

function notifyAutologin(data)
{
    if (data.status != 'ok') {
        return;
    }
    document.getElementsByTagName("body")[0]
        .insertAdjacentHTML(
            'beforeend',
            '<div id="autologinnotifier" class="alert alert-success"'
            + ' style="display: none; position: fixed; top: 0px; left: 0px; width: 100%; text-align: center">'
            + 'Welcome, ' + data.name + '.'
            + ' You have been logged in - '
            + '<a href="' + document.location + '">reload</a> to see it.'
            + '</div>'
        );
    $('#autologinnotifier').click(function(event) {
        $(this).fadeOut();
    });
    $('#autologinnotifier').hide().fadeIn('slow');
}
