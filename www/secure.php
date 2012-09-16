<?php
/** 
 * Protect page
 */
namespace phorkie;
require_once 'www-header.php';
$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['identity'])) {
    header("Location: /login");
}
?>
