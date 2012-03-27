<?php
namespace Phorkie;
/**
 * Display paste contents
 */
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

render(
    'display',
    array(
        'repo' => $repo,
    )
);
?>
