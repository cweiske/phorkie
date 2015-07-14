<?php
namespace phorkie;
/**
 * Edit paste contents
 */
$reqWritePermissions = true;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

$file = null;
if (isset($_GET['file'])) {
    if ($_GET['file'] == 'newfile') {
        $file = 'newfile';
    } else {
        $file = $repo->getFileByName($_GET['file']);
    }
}

$repopo = new Repository_Post($repo);
if ($repopo->process($_POST, $_SESSION)) {
    $anchor = '';
    if ($file instanceof File) {
        if (isset($repopo->renameMap[$file->getFilename()])) {
            $anchor = '#'
                . $repo->getFileByName(
                    $repopo->renameMap[$file->getFilename()]
                )->getAnchorName();
        } else {
            $anchor = '#' . $file->getAnchorName();
        }
    } else if ($file === 'newfile' && $repopo->newfileName) {
        $anchor = '#' . $repo->getFileByName($repopo->newfileName)->getAnchorName();
    }
    redirect($repo->getLink('display', null, true) . $anchor);
}

$actionFile = null;
if ($file instanceof File) {
    $actionFile = $file->getFilename();
} else if ($file === 'newfile') {
    $actionFile = 'newfile';
}

render(
    'edit',
    array(
        'repo' => $repo,
        'singlefile' => $file,
        'dh'   => new \Date_HumanDiff(),
        'htmlhelper' => new HtmlHelper(),
        'formaction' => $repo->getLink('edit', $actionFile)
    )
);
?>
