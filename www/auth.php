<?php
require_once 'www-header.php';
require_once $GLOBALS['phorkie']['auth']['google']['path'] . 'src/apiClient.php';
require_once $GLOBALS['phorkie']['auth']['google']['path'] . 'src/contrib/apiOauth2Service.php';

// 
$client = new apiClient();
$client->setApplicationName($GLOBALS['phorkie']['auth']['google']['appname']);
$client->setClientId($GLOBALS['phorkie']['auth']['google']['clientid']);
$client->setClientSecret($GLOBALS['phorkie']['auth']['google']['clientsecret']);
$client->setRedirectUri($GLOBALS['phorkie']['auth']['google']['redirecturi']);

$oauth2 = new apiOauth2Service($client);

if (isset($_GET['code'])) {
    $client->authenticate();
    $_SESSION['token'] = $client->getAccessToken();
    if ($client->getAccessToken()) {
        $user = $oauth2->userinfo->get();
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
    }
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SESSION['REQUEST_URI'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) {
    session_destroy();
    $redirect = $GLOBALS['phorkie']['auth']['google']['logouturi'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if ($client->getAccessToken()) {
    $user = $oauth2->userinfo->get();
    $_SESSION['token'] = $client->getAccessToken();
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
} else {
    $authUrl = $client->createAuthUrl();
    $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
}
?>
