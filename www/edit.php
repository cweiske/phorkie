<?php
namespace Phorkie;
/**
 * Edit paste contents
 */
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

render(
    'edit',
    array(
        'repo' => $repo,
    )
);
?>
