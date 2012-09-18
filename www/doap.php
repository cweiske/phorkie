<?php
namespace phorkie;
/**
 * Display DOAP of the paste.
 * Contains a machine-readable project description with Git URL.
 */
$securityLevel = '0';
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

header('Content-Type: application/rdf+xml');
render(
    'doap',
    array(
        'repo' => $repo,
        'date' => date('Y-m-d', end($repo->getHistory())->committerTime),
        'link' => Tools::fullUrl($repo->getLink('display'))
    )
);
?>
