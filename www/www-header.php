<?php
namespace phorkie;
error_reporting(error_reporting() & ~E_STRICT & ~E_DEPRECATED);
session_set_cookie_params(14 * 86400);//2 weeks session expiry time
session_start();

require_once __DIR__ . '/../src/phorkie/autoload.php';
set_exception_handler(
    function ($e) {
        if ($e instanceof Exception) {
            header('HTTP/1.0 ' . $e->httpStatusCode);
        } else {
            header('HTTP/1.0 500 Internal server error');
        }

        if (!isset($GLOBALS['twig'])) {
            echo '<h1>Exception</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo "\n";
            exit();
        }

        render(
            'exception',
            array(
                'exception' => $e,
                'debug'     => $GLOBALS['phorkie']['cfg']['debug']
            )
        );
        exit();
    }
);

require_once __DIR__ . '/../data/config.default.php';
$pharFile = \Phar::running();
if ($pharFile == '') {
    $cfgFilePath = __DIR__ . '/../data/config.php';
} else {
    //remove phar:// from the path
    $cfgFilePath = substr($pharFile, 7) . '.config.php';
}
$GLOBALS['phorkie']['cfgfiles'][$cfgFilePath] = false;
$GLOBALS['phorkie']['suggestSetupCheck'] = false;
if (file_exists($cfgFilePath)) {
    $GLOBALS['phorkie']['cfgfiles'][$cfgFilePath] = true;
    require_once $cfgFilePath;
} else if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    $GLOBALS['phorkie']['suggestSetupCheck'] = true;
}

if ($GLOBALS['phorkie']['cfg']['baseurl'] === null) {
    $GLOBALS['phorkie']['cfg']['baseurl'] = Tools::detectBaseUrl();
    if (substr($GLOBALS['phorkie']['cfg']['git']['public'], 0, 9) == '%BASEURL%') {
        //make autoconfig work
        $GLOBALS['phorkie']['cfg']['git']['public'] = Tools::fullUrlNoPhar(
            substr($GLOBALS['phorkie']['cfg']['git']['public'], 9)
        );
    }
}

// Set/Get git commit session variables
$_SESSION['ipaddr'] = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = $GLOBALS['phorkie']['auth']['anonymousName'];
}
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = $GLOBALS['phorkie']['auth']['anonymousEmail'];
}

\Twig_Autoloader::register();

$loader = new \Twig_Loader_Filesystem($GLOBALS['phorkie']['cfg']['tpl']);
$twig = new \Twig_Environment(
    $loader,
    array(
        //'cache' => '/path/to/compilation_cache',
        'debug' => true
    )
);
$twig->addFunction('ntext', new \Twig_Function_Function('\phorkie\ntext'));
function ntext($value, $singular, $plural)
{
    if (abs($value) == 1) {
        return sprintf($singular, $value);
    }
    return sprintf($plural, $value);
}
//$twig->addExtension(new \Twig_Extension_Debug());

if (!isset($noSecurityCheck) || $noSecurityCheck !== true) {
    require __DIR__ . '/www-security.php';
}

function render($tplname, $vars = array())
{
    $vars['baseurl'] = '/';
    if (!empty($GLOBALS['phorkie']['cfg']['baseurl'])) {
        $vars['baseurl'] = $GLOBALS['phorkie']['cfg']['baseurl'];
    }
    $vars['css'] = $GLOBALS['phorkie']['cfg']['css'];
    $vars['iconpng'] = $GLOBALS['phorkie']['cfg']['iconpng'];
    $vars['title'] = $GLOBALS['phorkie']['cfg']['title'];
    $vars['topbar'] = $GLOBALS['phorkie']['cfg']['topbar'];
    if (isset($_SESSION['identity'])) {
        $vars['identity'] = $_SESSION['identity'];
        $vars['name'] = $_SESSION['name'];
        $vars['email'] = $_SESSION['email'];
    } else if (isset($_COOKIE['lastopenid'])
        && !isset($_COOKIE['tried-autologin'])
    ) {
        $vars['autologin'] = true;
    }
    $vars['db'] = new Database();
    if (!isset($vars['htmlhelper'])) {
        $vars['htmlhelper'] = new HtmlHelper();
    }
    $vars['suggestSetupCheck'] = $GLOBALS['phorkie']['suggestSetupCheck'];

    $template = $GLOBALS['twig']->loadTemplate($tplname . '.htm');
    echo $template->render($vars);
}
function redirect($target)
{
    header('Location: ' . $target);
    exit();
}
?>
