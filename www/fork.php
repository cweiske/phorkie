<?php
/**
 * Fork a repository
 */
namespace phorkie;
$reqWritePermissions = true;
require_once 'www-header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception_Input('Forking only possible via POST');
}

$repo = new Repository();
$repo->loadFromRequest();

$forker = new Forker();
$new    = $forker->forkLocal($repo);

FlashMessage::save('Paste has been forked');
redirect($new->getLink('display', null, true));
?>
