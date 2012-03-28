<?php
namespace Phorkie;
/**
 * Show paste creation form
 *
 * Elements:
 * - description
 * - file name (default: default.php)
 * - content
 *
 * Creates and redirects to display page
 */
require_once 'www-header.php';

if (isset($_POST['files'])) {
    //save
    $rs = new Repositories();
    $repo = $rs->createNew();
    $vc = $repo->getVc();
    $vc->initRepository();
    foreach (glob($repo->repoDir . '/.git/hooks/*') as $hookfile) {
        unlink($hookfile);
    }
    $repo->setDescription($_POST['description']);

    foreach ($_POST['files'] as $num => $arFile) {
        if ($arFile['name'] != '') {
            //FIXME: fix file name from ..
            $fname = $arFile['name'];
        } else {
            $fname = 'phork' . $num . '.' . $arFile['type'];
        }
        $fpath = $repo->repoDir . $fname;
        file_put_contents($fpath, $arFile['content']);
        //fixme: let the class do that when it is able to
        $command = $vc->getCommand('add')
            ->addArgument($fname)
            ->execute();
    }
    $command = $vc->getCommand('commit')
        ->setOption('message', 'initial paste')
        ->setOption('author', 'Anonymous <anonymous@phorkie>')
        ->execute();
    //redirect to phork
    redirect($repo->getLink('display'));
}

$phork = array(
    '1' => new File(null, null)
);
render('index', array('files' => $phork, 'description' => ''));
?>