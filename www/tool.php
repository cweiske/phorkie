<?php
/**
 * Runs a tool on a file
 */
namespace phorkie;
require_once 'www-header.php';
$repo = new Repository();
$repo->loadFromRequest();

if (!isset($_GET['file']) || $_GET['file'] == '') {
    throw new Exception_Input('File name missing');
}
$file = $repo->getFileByName($_GET['file']);

if (!isset($_GET['tool']) || $_GET['tool'] == '') {
    throw new Exception_Input('Tool name missing');
}

$tm = new Tool_Manager();
$tool = $tm->loadTool($_GET['tool']);

$res = $tool->run($file);

render(
    'tool',
    array(
        'repo' => $repo,
        'file' => $file,
        'toolres' => $res,
    )
);

?>