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
        $error = $fr->error;
    } else if (false !== ($gitUrl = $fr->getUniqueGitUrl())) {
        if (isset($_POST['orig_url'])) {
            $fr->setUrl($_POST['orig_url']);
        }
        $forker = new Forker();
        try {
            $new = $forker->forkRemote($gitUrl, $fr->getUrl());
            redirect($new->getLink('display', null, true));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
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
