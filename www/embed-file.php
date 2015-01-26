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

if (!isset($_GET['file']) || $_GET['file'] == '') {
    throw new Exception_Input('File name missing');
}

$file = $repo->getFileByName($_GET['file']);
header('Content-Type: text/javascript');
header('Expires: ' . date('r', time() + 3600));
render(
    'embed-file',
    array(
        'repo' => $repo,
        'file' => $file,
    )
);
?>
