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

// A tool for testing Relying Party functionality
set_include_path(
    __DIR__ . '/../../src/'
    . PATH_SEPARATOR . get_include_path()
);

require_once 'www-header.php';
require_once 'openid/config.php';


if (isset($_REQUEST['logout'])) {
    unset($_SESSION);
    session_destroy();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	exit;
}

if (!count($_GET) && !count($_POST)) {
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . "/login";
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    exit;
}

// Hackaround Non-Javascript Login Page
if (!count($_POST) && isset($_GET['start'])) {
    $_POST = $_GET;
}

if (isset($_POST['identifier'])) {
    $identifier = $_POST['identifier'];
} else if (isset($_SESSION['identifier'])) {
    $identifier = $_SESSION['identifier'];
} else {
    $identifier = null;
}

try {
    $o = new OpenID_RelyingParty($returnTo, $realm, $identifier);
} catch (OpenID_Exception $e) {
    $contents  = "<div class='openid_results'>\n";
    $contents .= "<pre>" . $e->getMessage() . "</pre>\n";
    $contents .= "</div class='openid_results'>";
    include_once 'openid/wrapper.php';
    exit;
}

if (!empty($_POST['disable_associations'])
    || !empty($_SESSION['disable_associations'])) {

    $o->disableAssociations();
    $_SESSION['disable_associations'] = true;
}

$log = new OpenID_Observer_Log;
OpenID::attach($log);

if (isset($_POST['start'])) {

    $_SESSION['identifier'] = $identifier;
    try {
        $authRequest = $o->prepare();
    } catch (OpenID_Exception $e) {
        $contents  = "<div class='openid_results'>\n";
        $contents .= "<pre>" . $e->getMessage() . "</pre>\n";
        $contents .= "</div class='openid_results'>";
        include_once 'openid/wrapper.php';
        exit;
    }

    // checkid_immediate
    if (!empty($_POST['checkid_immediate'])) {
        $authRequest->setMode('checkid_immediate');
    }

    // SREG
    if (!empty($_POST['sreg'])) {
        $sreg = new OpenID_Extension_SREG11(OpenID_Extension::REQUEST);
        $sreg->set('required', 'email,firstname,lastname,nickname');
        $sreg->set('optional', 'gender,dob');
        $authRequest->addExtension($sreg);
    }

    // AX
    if (!empty($_POST['ax'])) {
        $ax = new OpenID_Extension_AX(OpenID_Extension::REQUEST);
        $ax->set('type.email', 'http://axschema.org/contact/email');
        $ax->set('type.firstname', 'http://axschema.org/namePerson/first');
        $ax->set('type.lastname', 'http://axschema.org/namePerson/last');
        $ax->set('mode', 'fetch_request');
        $ax->set('required', 'email,firstname,lastname');
        $authRequest->addExtension($ax);
    }

    // UI
    if (!empty($_POST['ui'])) {
        $ui = new OpenID_Extension_UI(OpenID_Extension::REQUEST);
        $ui->set('mode', 'popup');
        $ui->set('language', 'en-US');
        $authRequest->addExtension($ui);
    }

    // OAuth
    if (!empty($_POST['oauth'])) {
        $oauth = new OpenID_Extension_OAuth(OpenID_Extension::REQUEST);
        $oauth->set('consumer', $_POST['oauth_consumer_key']);
        $_SESSION['OAuth_consumer_key']    = $_POST['oauth_consumer_key'];
        $_SESSION['OAuth_consumer_secret'] = $_POST['oauth_consumer_secret'];

        $oauth->set('scope', $_POST['oauth_scope']);
        $_SESSION['OAuth_scope'] = $_POST['oauth_scope'];

        $_SESSION['OAuth_access_token_url']    = $_POST['oauth_access_token_url'];
        $_SESSION['OAuth_access_token_method'] = $_POST['oauth_access_token_method'];

        $authRequest->addExtension($oauth);
    }
    
    $url = $authRequest->getAuthorizeURL();
    
    if (empty($_POST['debug'])) {
        header("Location: $url");
        exit;
    }
    
} else {
    if (isset($_SESSION['identifier'])) {
        $usid = $_SESSION['identifier'];
        unset($_SESSION['identifier']);
    } else {
        $usid = null;
    }

    unset($_SESSION['disable_associations']);

    if (!count($_POST)) {
        list(, $queryString) = explode('?', $_SERVER['REQUEST_URI']);
    } else {
        // I hate php sometimes
        $queryString = file_get_contents('php://input');
    }

    $message = new OpenID_Message($queryString, OpenID_Message::FORMAT_HTTP);
    $id      = $message->get('openid.claimed_id');
    $mode    = $message->get('openid.mode');

    try {
        $result = $o->verify(new Net_URL2($returnTo . '?' . $queryString),
                                          $message);

        if ($result->success()) {
            $status  = "<tr><td>Status:</td><td><font color='green'>SUCCESS!";
            $status .= " ({$result->getAssertionMethod()})</font></td></tr>";
        } else {
            $status  = "<tr><td>Status:</td><td><font color='red'>FAIL!";
            $status .= " ({$result->getAssertionMethod()})</font></td></tr>";
        }
    } catch (OpenID_Exception $e) {
        $status  = "<tr><td>Status:</td><td><font color='red'>EXCEPTION!";
        $status .= " ({$e->getMessage()} : {$e->getCode()})</font></td></tr>";
    }

    // OAuth hyprid fetching access token
    if (isset($_SESSION['OAuth_consumer_key'],
              $_SESSION['OAuth_consumer_secret'],
              $_SESSION['OAuth_access_token_url'],
              $_SESSION['OAuth_access_token_method'])) {

        try {
            $oauth = new OpenID_Extension_OAuth(OpenID_Extension::RESPONSE,
                                                $message);

            // Fix line lengths.
            $consumerKey    = $_SESSION['OAuth_consumer_key'];
            $consumerSecret = $_SESSION['OAuth_consumer_key'];
            $tokenURL       = $_SESSION['OAuth_access_token_url'];
            $tokenMethod    = $_SESSION['OAuth_access_token_method'];

            $oauthData = $oauth->getAccessToken($consumerKey,
                                                $consumerSecret,
                                                $tokenURL,
                                                array(),
                                                $tokenMethod);

        } catch (Exception $e) {
        }
    }

    $contents = "<div class='openid_results'>
    <p>
    <table>
    <tr colspan=2><td><b>Results</b></td></tr>
    <tr><td>User Supplied Identifier:</td><td>$usid</td></tr>
    <tr><td>Claimed Identifier:</td><td>$id</td></tr>
    <tr><td>Mode:</td><td>$mode</td></tr>
    $status\n
    <tr colspan=2><td><p><br><b>Message Contents</b></td></tr>";

    foreach ($message->getArrayFormat() as $key => $value) {
        $contents .= "<tr><td align=left>$key</td><td>$value</td></tr>\n";
    }

    if (isset($oauthData) && count($oauthData)) {
        $contents .= "<tr colspan=2>";
        $contents .= "    <td><p><br><b>OAuth Access token/secret</b></td>";
        $contents .= "</tr>";

        foreach ($oauthData as $key => $value) {
            $contents .= "<tr><td align=left>$key</td><td>$value</td></tr>\n";
        }
    }

    $contents .= "</table>";
    $contents .= "</div>";

    $openid = $message->getArrayFormat();
    if ($GLOBALS['phorkie']['auth']['secure'] > 0 &&
        $GLOBALS['phorkie']['auth']['userlist']) {
		if (!in_array($openid['openid.identity'], $GLOBALS['phorkie']['users'])) {
            $redirect = 'http://' . $_SERVER['HTTP_HOST'] . "/forbidden";
            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
			exit;
        }
    }
    // include_once 'openid/wrapper.php';

	$email = (isset($openid['openid.ext1.value.email'])) ? $openid['openid.ext1.value.email'] : null;
    $email = (isset($openid['openid.ext2.value.email']) && !isset($email)) ? $openid['openid.ext2.value.email'] : $email;
    $email = (isset($openid['openid.sreg.email']) && !isset($email)) ? $openid['openid.sreg.email'] : $email;
    $email = (isset($openid['openid.ax.value.email']) && !isset($email)) ? $openid['openid.ax.value.email'] : $email;
    $_SESSION['email'] = (isset($email)) ? $email : $GLOBALS['phorkie']['auth']['anonymousEmail'];

    $name = (isset($openid['openid.ext1.value.firstname']) && isset($openid['openid.ext1.value.lastname'])) ? $openid['openid.ext1.value.firstname']." ".$openid['openid.ext1.value.lastname'] : null;
    $name = (isset($openid['openid.sreg.firstname']) && isset($openid['openid.sreg.lastname']) && !isset($name)) ? $openid['openid.sreg.firstname']." ".$openid['openid.sreg.lastname'] : $name;
    $name = (isset($openid['openid.sreg.nickname']) && !isset($name)) ? $openid['openid.sreg.nickname'] : $name;
    $_SESSION['name'] = (isset($name)) ? $name : $_SERVER['REMOTE_ADDR'];

    $_SESSION['identity'] = $openid['openid.identity'];

    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SESSION['REQUEST_URI'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

?>
