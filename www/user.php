<?php
/**
 * Edit user information
 */
namespace phorkie;
$reqWritePermissions = true;
require_once 'www-header.php';
if (!isset($_SESSION['identity'])) {
    require 'forbidden.php';
}

render(
    'user',
    array(
        'identity' => $_SESSION['identity'],
        'name'     => $_SESSION['name'],
        'email'    => $_SESSION['email']
    )
);
?>
