<?php
require_once __DIR__ . '/../data/config.default.php';
require_once 'VersionControl/Git.php';
require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem($GLOBALS['phorkie']['cfg']['tpl']);
$twig = new Twig_Environment(
    $loader,
    array(
        //'cache' => '/path/to/compilation_cache',
        'debug' => true
    )
);

function render($tplname, $vars)
{
    $template = $GLOBALS['twig']->loadTemplate($tplname . '.htm');
    echo $template->render($vars);
}
function redirect($target)
{
    header('Location: /' . $target);
    exit();
}
function errout($statusCode, $message)
{
    header('HTTP/1.0 ' . $statusCode);
    echo $message;
    exit();
}
function get_type_from_file($file)
{
    return substr($file, strrpos($file, '.') + 1);
}
?>