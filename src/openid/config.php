<?php
/**
 * OpenID 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

set_include_path(dirname(__FILE__) . '/../../:' . get_include_path());

/**
 * Required files
 */
require_once 'OpenID/RelyingParty.php';
require_once 'OpenID/Discover.php';
require_once 'OpenID/Store.php';
require_once 'OpenID/Extension/SREG10.php';
require_once 'OpenID/Extension/SREG11.php';
require_once 'OpenID/Extension/AX.php';
require_once 'OpenID/Extension/UI.php';
require_once 'OpenID/Extension/OAuth.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Observer/Log.php';
require_once 'Net/URL2.php';

// Determine realm and return_to
$base = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $base .= 's';
}
$base .= '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];

$realm    = $base . '/';
$returnTo = $base . dirname($_SERVER['PHP_SELF']);
if ($returnTo[strlen($returnTo) - 1] != '/') {
    $returnTo .= '/';
}
$returnTo .= 'auth';

// SQL storage example
// $storeOptions = array(
//     'dsn' => 'mysql://user:pass@db.example.com/openid'
// );
// OpenID::setStore(OpenID_Store::factory('MDB2', $storeOptions));
//
// // The first time you run it, you'll also need to create the tables:
// OpenID::getStore()->createTables();

?>
