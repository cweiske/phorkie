<?php
namespace phorkie;
/**
 * Displays a file
 */
$securityLevel = '0';
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
if ($repo->hash === null) {
    //IIRC readfile is not so memory-intensive for big files
    readfile($file->getFullPath());
} else {
    echo $file->getContent();
}
?>
