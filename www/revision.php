<?php
namespace phorkie;
/**
 * Display historic paste contents
 */
$secureAtLevel = '0';
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

render(
    'revision',
    array(
        'repo' => $repo,
        'dh'   => new \Date_HumanDiff(),
    )
);
?>
