<?php
/**
 * Displays a file
 */
namespace phorkie;
require_once 'www-header.php';
$repo = new Repository();
$repo->loadFromRequest();

if (!isset($_GET['file']) || $_GET['file'] == '') {
    throw new Exception_Input('File name missing');
}

$file = $repo->getFileByName($_GET['file']);
$mimetype = $file->getMimeType();
if ($mimetype === null) {
    $mimetype = 'text/plain';
}
header('Content-Type: ' . $mimetype);
readfile($file->getFullPath());
?>
