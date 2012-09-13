<?php
require_once $GLOBALS['phorkie']['auth']['google']['path'] . 'src/apiClient.php';
require_once $GLOBALS['phorkie']['auth']['google']['path'] . 'src/contrib/apiOauth2Service.php';

$client = new apiClient();
$client->setApplicationName($GLOBALS['phorkie']['auth']['google']['appname']);
$client->setClientId($GLOBALS['phorkie']['auth']['google']['clientid']);
$client->setClientSecret($GLOBALS['phorkie']['auth']['google']['clientsecret']);
$client->setRedirectUri($GLOBALS['phorkie']['auth']['google']['redirecturi']);

$oauth2 = new apiOauth2Service($client);

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()) {
    $user = $oauth2->userinfo->get();
    $_SESSION['token'] = $client->getAccessToken();
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
} else {
    $authUrl = $client->createAuthUrl();
    $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
    error_log(session_id());
	header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
}
?>
