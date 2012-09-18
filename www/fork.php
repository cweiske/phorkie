<?php
/**
 * Fork a repository
 */
namespace phorkie;
$pageRequiresLogin = '1';
require_once 'www-header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception_Input('Forking only possible via POST');
}

$repo = new Repository();
$repo->loadFromRequest();

$rs = new Repositories();
$new = $rs->createNew();
$vc = $new->getVc();
\rmdir($new->gitDir);//VersionControl_Git wants an existing dir, git clone not
$vc->getCommand('clone')
    //this should be setOption, but it fails with a = between name and value
    ->addArgument('--separate-git-dir')
    ->addArgument($GLOBALS['phorkie']['cfg']['gitdir'] . '/' . $new->id . '.git')
    ->addArgument($repo->gitDir)
    ->addArgument($new->workDir)
    ->execute();
\copy($repo->gitDir . '/description', $new->gitDir . '/description');
foreach (\glob($new->gitDir . '/hooks/*') as $hookfile) {
    \unlink($hookfile);
}

//FIXME: where to put fork source link?
redirect($new->getLink('display'));
?>
