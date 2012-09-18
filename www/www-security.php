<?php
namespace phorkie;
/**
 * security levels + login requirement:
 */

if (!isset($GLOBALS['phorkie']['auth']['secure'])) {
    //not set? highest level of security
    $GLOBALS['phorkie']['auth']['secure'] = 2;
}

if ($GLOBALS['phorkie']['auth']['secure'] == 0) {
    //everyone may do everything
    return;
}

$logged_in = false;
if (!isset($_SESSION['identity'])) {
    //not logged in 
} else if ($GLOBALS['phorkie']['auth']['userlist']) {
    if (in_array($_SESSION['identity'], $GLOBALS['phorkie']['users'])) {
        $logged_in = true;
    }
} else {
    //session identity exists, no special checks required
    $logged_in = true;
}

if ($secureAtLevel >= $GLOBALS['phorkie']['auth']['secure']) {
    if ($logged_in) {
        return;
    }
} else {
    return;
}

// p / G / log_in = disp
// 0 / 1 / true   = return
// 0 / 1 / false  = block
// 0 / 2 / true   = return
// 0 / 2 / false  = return
// 1 / 1 / true   = return
// 1 / 1 / false  = block
// 1 / 2 / true   = return
// 1 / 2 / false  = block

$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
require 'forbidden.php';
?>
