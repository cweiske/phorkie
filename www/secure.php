<?php
/** 
 * Protect page
 */
namespace phorkie;
require_once 'www-header.php';
$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['identity'])) {
    header("Location: /login");
    exit;
}
if ($GLOBALS['phorkie']['auth']['secure'] > 0 &&
    $GLOBALS['phorkie']['auth']['userlist']) {
    if (!in_array($_SESSION['identity'], $GLOBALS['phorkie']['users'])) {
        $redirect = 'http://' . $_SERVER['HTTP_HOST'] . "/forbidden";
        header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
        exit;
    }
}
?>
