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

if (isset($_POST['file'])) {
    //save
    $rs = new Repositories();
    $repo = $rs->createNew();
    $vc = $repo->getVc();
    $vc->initRepository();
    foreach (glob($repo->repoDir . '/.git/hooks/*') as $hookfile) {
        unlink($hookfile);
    }
    file_put_contents($repo->repoDir . '.git/description', $_POST['description']);

    foreach ($_POST['file'] as $num => $arFile) {
        if ($arFile['name'] != '') {
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
    '1' => array(
        'filename' => '',
        'content' => '',
        'type' => ''
    )
);
render('index', array('file' => $phork, 'description' => ''));
?>