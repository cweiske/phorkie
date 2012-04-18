<?php
namespace phorkie;
set_include_path(
    __DIR__ . '/../src/'
    . PATH_SEPARATOR . get_include_path()
);
spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        $hdl = @fopen($file, 'r', true);
        if ($hdl !== false) {
            fclose($hdl);
            require $file;
        }
    }
);
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
if (file_exists(__DIR__ . '/../data/config.php')) {
    require_once __DIR__ . '/../data/config.php';
}
if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    SetupCheck::run();
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

function render($tplname, $vars)
{
    $vars['css'] = $GLOBALS['phorkie']['cfg']['css'];
    $vars['title'] = $GLOBALS['phorkie']['cfg']['title'];
    $vars['topbar'] = $GLOBALS['phorkie']['cfg']['topbar'];

    $template = $GLOBALS['twig']->loadTemplate($tplname . '.htm');
    echo $template->render($vars);
}
function redirect($target)
{
    header('Location: ' . $target);
    exit();
}
?>