<?php
namespace phorkie;
/**
 * Display paste contents
 */
require_once 'www-header.php';
if ($GLOBALS['phorkie']['auth']['secure'] == 2) {
    require_once 'secure.php';
}

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
