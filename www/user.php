<?php
/**
 * Edit user information
 */
namespace phorkie;
require_once 'www-header.php';
if (!isset($_SESSION['identity'])) {
    require_once 'secure.php';
}

if (isset($_POST['name'])) {
    $_SESSION['name'] = substr(filter_var($_POST['name'], FILTER_SANITIZE_STRING), 0, 35);
}

if (isset($_POST['email'])) {
    $_SESSION['email'] = substr(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL), 0, 35);
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
