<?php
namespace Phorkie;
/**
 * Edit paste contents
 */
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

if (isset($_POST['files'])) {
    $vc = $repo->getVc();
    $repo->setDescription($_POST['description']);

    $bChanged = false;
    foreach ($_POST['files'] as $num => $arFile) {
        if (!isset($arFile['original_name'])
            || !$repo->hasFile($arFile['original_name'])
        ) {
            //FIXME: Show error message
            continue;
        }
        //FIXME: fix file names from .. and ./
        if ($arFile['original_name'] != $arFile['name']) {
            //FIXME: what to do with overwrites?
            $vc->getCommand('mv')
                ->addArgument($arFile['original_name'])
                ->addArgument($arFile['name'])
                ->execute();
            $bChanged = true;
        }
        $file = $repo->getFileByName($arFile['name']);
        if ($file->getContent() != $arFile['content']) {
            file_put_contents($file->getPath(), $arFile['content']);
            $command = $vc->getCommand('add')
                ->addArgument($file->getFilename())
                ->execute();
            $bChanged = true;
        }
    }

    if ($bChanged) {
        $vc->getCommand('commit')
            ->setOption('message', '')
            ->setOption('allow-empty-message')
            ->setOption('author', 'Anonymous <anonymous@phorkie>')
            ->execute();
    }

    redirect($repo->getLink('display'));
}

render(
    'edit',
    array(
        'repo' => $repo,
    )
);
?>
