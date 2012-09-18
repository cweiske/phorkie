<?php
namespace phorkie;
/**
 * security levels + login requirement:
 */

if (!isset($GLOBALS['phorkie']['auth']['securityLevel'])) {
    //not set? highest level of security
    $GLOBALS['phorkie']['auth']['securityLevel'] = 2;
}

if ($GLOBALS['phorkie']['auth']['securityLevel'] == 0) {
    //everyone may do everything
    return;
}

$logged_in = false;
if (!isset($_SESSION['identity'])) {
    //not logged in 
} else if ($GLOBALS['phorkie']['auth']['listedUsersOnly']) {
    if (in_array($_SESSION['identity'], $GLOBALS['phorkie']['auth']['users'])) {
        $logged_in = true;
    }
} else {
    //session identity exists, no special checks required
    $logged_in = true;
}

if ($logged_in) {
    //you may do everything if you're logged in
    return;
}

if (!isset($reqWritePermissions)) {
    $reqWritePermissions = true;
}
if ($GLOBALS['phorkie']['auth']['securityLevel'] == 1
    && !$reqWritePermissions
) {
    return;
}

$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
require 'forbidden.php';
?>
