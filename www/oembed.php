<?php
namespace phorkie;
/**
 * Embed a paste via oEmbed
 *
 * @link http://www.oembed.com/
 */
$reqWritePermissions = false;
require_once 'www-header.php';

if (!isset($_GET['url'])) {
    header('HTTP/1.0 400 Bad Request');
    echo "url parameter missing\n";
    exit(1);
}

if (!isset($_GET['format'])) {
    $format = 'json';
} else if ($_GET['format'] != 'json' && $_GET['format'] != 'xml') {
    header('HTTP/1.0 400 Bad Request');
    echo "Invalid format parameter\n";
    exit(1);
} else {
    $format = $_GET['format'];
}

if (!isset($_GET['maxwidth'])) {
    $maxWidth = 900;
} else {
    $maxWidth = (int) $_GET['maxwidth'];
    if ($maxWidth <= 100) {
        header('HTTP/1.0 400 Bad Request');
        echo "maxwidth parameter too small\n";
        exit(1);
    }
}

if (!isset($_GET['maxheight'])) {
    $maxHeight = 900;
} else {
    $maxHeight = (int) $_GET['maxheight'];
    if ($maxHeight <= 100) {
        header('HTTP/1.0 400 Bad Request');
        echo "maxheight parameter too small\n";
        exit(1);
    }
}


$parts = explode('/', $_GET['url']);
$id = end($parts);

$repo = new Repository();
$repo->loadById($id);

if ($format == 'json') {
    $data = new \stdClass();
} else {
    $data = new \SimpleXMLElement(
        '<?xml version="1.0" encoding="utf-8" standalone="yes"?>'
        . '<oembed/>'
    );
}
$data->type = 'rich';
$data->version = '1.0';

$data->provider_name = 'phorkie';
$data->provider_url = Tools::fullUrl();

$data->title = $repo->getTitle();
$author = $repo->getOwner();
$data->author_name = $author['name'];
$data->cache_age = 86400;

$data->width  = $maxWidth;
$data->height = $maxHeight;

$data->html = render('oembed', array('repo' => $repo), true);

if ($format == 'json') {
    header('Content-type: application/json');
    echo json_encode($data) . "\n";
} else {
    header('Content-type: text/xml');
    echo $data->asXML();;
}
?>
