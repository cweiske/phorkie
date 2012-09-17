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

if ($logged_in) {
    //logged in? all fine
    return;
} else if ($GLOBALS['phorkie']['auth']['secure'] == 2) {
    //not logged in and security level 2 => error
    require 'forbidden.php';
} else if (isset($pageRequiresLogin) && !$pageRequiresLogin) {
    return;
}

require 'forbidden.php';
?>