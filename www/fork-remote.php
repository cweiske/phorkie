<?php
namespace phorkie;
/**
 * Fork a remote repository.
 * Displays a URL selection form when multiple git urls have been found
 */
require_once 'www-header.php';

$error = null;
$urls  = null;
if (isset($_POST['remote_url'])) {
    $fr = new ForkRemote($_POST['remote_url']);
    if (false === $fr->parse()) {
        //no url found
        $error = 'No git:// clone URL found';
    } else if (false !== ($gitUrl = $fr->getUniqueGitUrl())) {
        if (isset($_POST['orig_url'])) {
            $fr->setUrl($_POST['orig_url']);
        }
        $forker = new Forker();
        $new    = $forker->forkRemote($gitUrl, $fr->getUrl());
        redirect($new->getLink('display'));
    } else {
        //multiple urls found
        $urls = $fr->getGitUrls();
    }
}

$selsize = 0;
if (is_array($urls)) {
    foreach ($urls as $group) {
        ++$selsize;
        if (count($group) > 1) {
            $selsize += count($group);
        }
    }
}

render(
    'fork-remote',
    array(
        'remote_url' => isset($_POST['remote_url']) ? $_POST['remote_url'] : '',
        'error'      => $error,
        'urls'       => $urls,
        'urlselsize' => $selsize,
    )
);
?>
