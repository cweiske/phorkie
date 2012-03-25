<?php
/**
 * Display paste contents
 *
 */
require_once 'www-header.php';

if (!isset($_GET['id'])) {
    errout(400, 'Paste ID missing');
}
if (!is_numeric($_GET['id'])) {
    errout(400, 'Paste ID not numeric');
}
$id = (int)$_GET['id'];
$repoDir = $GLOBALS['phorkie']['cfg']['repos'] . '/' . $id;
if (!is_dir($repoDir)) {
    errout(404, 'Paste not found');
}

$files = glob($repoDir . '/*');
$tplFiles = array();
foreach ($files as $file) {
    $tplFile = array();
    $tplFile['filename'] = basename($file);
    $tplFile['type'] = get_type_from_file($file);
    //FIXME: highlight
    $tplFile['content'] = file_get_contents($file);
    $tplFile['raw'] = '/' . $id . '/raw/' . $tplFile['filename'];
    $tplFiles[] = $tplFile;
}

render(
    'display',
    array(
        'description' => file_get_contents($repoDir . '/.git/description'),
        'files' => $tplFiles,
        'links' => array(
            'edit' => '/' . $id . '/edit'
        )
    )
);
?>
