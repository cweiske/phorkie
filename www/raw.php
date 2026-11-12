<?php
namespace phorkie;
/**
 * Displays a file
 */
$reqWritePermissions = false;
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
// The raw view echoes user-pasted content verbatim. Browsers execute some types inline
// (text/html, image/svg+xml, XML, JavaScript) in the application's own origin, which is a stored
// XSS vector, so serve those as plain text and stop content-type sniffing.
$aExecutableTypes = array(
    'text/html', 'application/xhtml+xml', 'image/svg+xml',
    'application/xml', 'text/xml', 'application/javascript',
    'text/javascript', 'application/x-javascript', 'application/ecmascript',
);
header('X-Content-Type-Options: nosniff');
if (in_array($mimetype, $aExecutableTypes)) {
    $mimetype = 'text/plain; charset=utf-8';
}
header('Content-Type: ' . $mimetype);
if ($repo->hash === null) {
    //IIRC readfile is not so memory-intensive for big files
    readfile($file->getFullPath());
} else {
    echo $file->getContent();
}
?>
