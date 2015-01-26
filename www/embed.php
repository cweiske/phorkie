<?php
namespace phorkie;
/**
 * Embed a file of a paste into a HTML site.
 * We deliver javascript for that.
 */
$reqWritePermissions = false;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

header('Content-Type: text/javascript');
header('Expires: ' . date('r', time() + 3600));
render(
    'embed',
    array(
        'repo' => $repo,
    )
);
?>
