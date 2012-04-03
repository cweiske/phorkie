<?php
/**
 * Fork a repository
 */
namespace phorkie;
require_once 'www-header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception_Input('Forking only possible via POST');
}

$repo = new Repository();
$repo->loadFromRequest();

$rs = new Repositories();
$new = $rs->createNew();
$new->getVc()->getCommand('clone')
    ->addArgument($repo->repoDir)
    ->addArgument($new->repoDir)
    ->execute();
\copy($repo->repoDir . '/.git/description', $new->repoDir . '/.git/description');
foreach (glob($new->repoDir . '/.git/hooks/*') as $hookfile) {
    unlink($hookfile);
}

//FIXME: where to put fork source link?
redirect($new->getLink('display'));
?>