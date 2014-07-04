<?php
namespace phorkie;
/**
 * Show help
 */
$reqWritePermissions = false;
require_once 'www-header.php';

render(
    'help',
    array(
        'htmlhelper' => new HtmlHelper(),
        'publicGitUrl' => @$GLOBALS['phorkie']['cfg']['git']['public'],
    )
);
?>
