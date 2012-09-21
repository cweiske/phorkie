<?php
namespace phorkie;
$noSecurityCheck = true;
require_once 'www-header.php';

if (isset($_REQUEST['logout'])) {
    unset($_SESSION);
    session_destroy();
    header('Location: ' . Tools::fullUrl('/'));
    exit();
}

if (!count($_GET) && !count($_POST)) {
    render('login');
    exit();
}

// Hackaround Non-Javascript Login Page
if (!count($_POST) && isset($_GET['openid_url'])) {
    $_POST = $_GET;
}

if (isset($_POST['openid_url'])) {
    $openid_url = $_POST['openid_url'];
} else if (isset($_SESSION['openid_url'])) {
    $openid_url = $_SESSION['openid_url'];
} else {
    $openid_url = null;
}

$realm    = Tools::fullUrl('/');
$returnTo = Tools::fullUrl('/login');

try {
    $o = new \OpenID_RelyingParty($returnTo, $realm, $openid_url);
} catch (OpenID_Exception $e) {
    throw new Exception($e->getMessage());
}

if (!empty($_POST['disable_associations']) || !empty($_SESSION['disable_associations'])) {
    $o->disableAssociations();
    $_SESSION['disable_associations'] = true;
}

if (isset($_POST['openid_url'])) {

    $_SESSION['openid_url'] = $openid_url;
    try {
        $authRequest = $o->prepare();
    } catch (OpenID_Exception $e) {
        throw new Exception($e->getMessage());
    }

    // SREG
    $sreg = new \OpenID_Extension_SREG11(\OpenID_Extension::REQUEST);
    $sreg->set('required', 'email,fullname');
    $authRequest->addExtension($sreg);

    // AX, http://stackoverflow.com/a/7657061/282601
    $ax = new \OpenID_Extension_AX(\OpenID_Extension::REQUEST);
    $ax->set('type.email', 'http://axschema.org/contact/email');
    $ax->set('type.firstname', 'http://axschema.org/namePerson/first');
    $ax->set('type.lastname', 'http://axschema.org/namePerson/last');
    $ax->set('type.fullname', 'http://axschema.org/namePerson');
    $ax->set('mode', 'fetch_request');
    $ax->set('required', 'email,firstname,lastname,fullname');
    $authRequest->addExtension($ax);

    $url = $authRequest->getAuthorizeURL();

    header("Location: $url");
    exit;
    
}

if (isset($_SESSION['openid_url'])) {
    $usid = $_SESSION['openid_url'];
    unset($_SESSION['openid_url']);
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

$message = new \OpenID_Message($queryString, \OpenID_Message::FORMAT_HTTP);
$id      = $message->get('openid.claimed_id');
$mode    = $message->get('openid.mode');

try {
    $result = $o->verify(new \Net_URL2($returnTo . '?' . $queryString), $message);

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


$openid = $message->getArrayFormat();

$email = isset($openid['openid.ext1.value.email'])
    ? $openid['openid.ext1.value.email']
    : null;
$email = isset($openid['openid.ext2.value.email']) && !isset($email)
    ? $openid['openid.ext2.value.email']
    : $email;
$email = isset($openid['openid.sreg.email']) && !isset($email)
    ? $openid['openid.sreg.email']
    : $email;
$email = isset($openid['openid.ax.value.email'])
    && isset($openid['openid.ax.type.email'])
    && $openid['openid.ax.type.email'] == 'http://axschema.org/contact/email'
    && !isset($email)
    ? $openid['openid.ax.value.email']
    : $email;
$_SESSION['email'] = isset($email)
    ? $email
    : $GLOBALS['phorkie']['auth']['anonymousEmail'];

$name = isset($openid['openid.ext1.value.firstname'])
    && isset($openid['openid.ext1.value.lastname'])
    ? $openid['openid.ext1.value.firstname'] . ' '
    . $openid['openid.ext1.value.lastname']
    : null;
$name = isset($openid['openid.sreg.fullname']) && !isset($name)
    ? $openid['openid.sreg.fullname']
    : $name;
$name = isset($openid['openid.ax.value.fullname'])
    && isset($openid['openid.ax.type.fullname'])
    && $openid['openid.ax.type.fullname'] == 'http://axschema.org/namePerson'
    && !isset($name)
    ? $openid['openid.ax.value.fullname']
    : $name;

$_SESSION['name'] = isset($name) ? $name : $_SERVER['REMOTE_ADDR'];
$_SESSION['identity'] = $openid['openid.identity'];

if (isset($_SESSION['REQUEST_URI'])) {
    $redirect = Tools::fullUrl($_SESSION['REQUEST_URI']);
} else {
    $redirect = Tools::fullUrl('/');
}
header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
exit;
?>
