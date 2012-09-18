<?php
namespace phorkie;
/**
 * Fork a remote repository.
 * Displays a URL selection form when multiple git urls have been found
 */
require_once 'www-header.php';

if (isset($_POST['remote_url'])) {
    $fr = new ForkRemote($_POST['remote_url']);
    $fr->parse();
    if ($fr->hasUniqueGitUrl()) {
        //FIXME: fork
    }
    //FIXME: display error or selection list
}

render(
    'fork-remote',
    array(
        'remote_url' => isset($_POST['remote_url']) ? $_POST['remote_url'] : ''
    )
);
?>
