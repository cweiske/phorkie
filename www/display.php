<?php
namespace phorkie;
/**
 * Display paste contents
 */
$reqWritePermissions = false;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

render(
    'display',
    array(
        'repo' => $repo,
        'dh'   => new \Date_HumanDiff(),
    )
);
?>
