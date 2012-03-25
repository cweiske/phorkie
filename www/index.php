<?php
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
    $repoDir = $GLOBALS['phorkie']['cfg']['repos'];
    $n = count(glob($repoDir . '/*', GLOB_ONLYDIR));
    $dir = $repoDir . '/' . $n . '/'; 
    mkdir($dir, 0777);//FIXME
    $vc = new VersionControl_Git($dir);
    $vc->initRepository();
    file_put_contents($dir . '.git/description', $_POST['description']);

    foreach ($_POST['file'] as $num => $arFile) {
        if ($arFile['name'] != '') {
            $fname = $arFile['name'];
        } else {
            $fname = 'phork' . $num . '.' . $arFile['type'];
        }
        $fpath = $dir . $fname;
        file_put_contents($fpath, $arFile['content']);
        //fixme: let the class do that when it is able to
        $command = $vc->getCommand('add')
            ->addArgument($fname)
            ->execute();
    }
    $command = $vc->getCommand('commit')
        ->setOption('message', 'initial paste')
        ->execute();
    //redirect to phork
    redirect($n);
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