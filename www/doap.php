<?php
namespace phorkie;
/**
 * Display DOAP of the paste.
 * Contains a machine-readable project description with Git URL.
 */
$reqWritePermissions = false;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

$history = $repo->getHistory();

header('Content-Type: application/rdf+xml');
render(
    'doap',
    array(
        'repo' => $repo,
        'date' => date('Y-m-d', end($history)->committerTime),
        'link' => Tools::fullUrl($repo->getLink('display'))
    )
);
?>
