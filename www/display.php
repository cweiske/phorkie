<?php
namespace phorkie;
/**
 * Display paste contents
 */
require_once 'www-header.php';
if ($GLOBALS['phorkie']['auth']['secure'] == 2) {
    include_once 'secure.php';
}

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
