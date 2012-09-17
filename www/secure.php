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
        header('HTTP/1.1 403 Forbidden');
        $db = new Database();
        render(
            'forbidden',
            array(
                'recents'     => $db->getSearch()->listAll(0, 5, 'crdate', 'desc'),
            )
        );
        exit;
    }
}
?>
