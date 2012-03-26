<?php
namespace Phorkie;
/**
 * Display paste contents
 */
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

/*
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
*/

render(
    'display',
    array(
        'repo' => $repo,
        /*
        'description' => file_get_contents($repoDir . '/.git/description'),
        'files' => $tplFiles,
        'links' => array(
            'edit' => '/' . $id . '/edit'
        )
        */
    )
);
?>
