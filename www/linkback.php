<?php
namespace phorkie;
/**
 * Receive linkback
 */
$reqWritePermissions = false;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

$s = new \PEAR2\Services\Linkback\Server();
$s->addCallback(new Repository_LinkbackReceiver($repo));
$s->run();
?>