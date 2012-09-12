<?php
namespace phorkie;
/**
 * Edit paste contents
 */
require_once 'www-header.php';
if ($GLOBALS['phorkie']['auth']['method'] == "google") { require_once 'secure.php'; }

$repo = new Repository();
$repo->loadFromRequest();

$repopo = new Repository_Post($repo);
if ($repopo->process($_POST)) {
    redirect($repo->getLink('display'));
}

render(
    'edit',
    array(
        'repo' => $repo,
        'htmlhelper' => new HtmlHelper(),
    )
);
?>
