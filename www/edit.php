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
    $file = $repo->getFileByName($_GET['file']);
}

$repopo = new Repository_Post($repo);
if ($repopo->process($_POST, $_SESSION)) {
    $anchor = '';
    if ($file !== null) {
        $anchor = '#' . $file->getAnchorName();
    }
    redirect($repo->getLink('display', null, true) . $anchor);
}

render(
    'edit',
    array(
        'repo' => $repo,
        'singlefile' => $file,
        'dh'   => new \Date_HumanDiff(),
        'htmlhelper' => new HtmlHelper(),
    )
);
?>
