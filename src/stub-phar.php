<?php
/**
 * Phar stub file for phorkie. Handles startup of the .phar file.
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Phorkie
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://phorkie.sf.net/
 */
if (!in_array('phar', stream_get_wrappers()) || !class_exists('Phar', false)) {
    echo "Phar extension not avaiable\n";
    exit(255);
}

$web = 'www/index.php';
//FIXME
$cli = 'scripts/index.php';

/**
 * Rewrite the HTTP request path to an internal file.
 * Maps "" and "/" to "www/index.php".
 *
 * @param string $path Path from the browser, relative to the .phar
 *
 * @return string Internal path.
 */
function rewritePath($path)
{
    if ($path == '') {
        //we need a / to get the relative links on index.php work
        if (!isset($_SERVER['REQUEST_SCHEME'])) {
            $_SERVER['REQUEST_SCHEME'] = 'http';
        }
        $url = $_SERVER['REQUEST_SCHEME'] . '://'
            . $_SERVER['HTTP_HOST']
            . preg_replace('/[?#].*$/', '', $_SERVER['REQUEST_URI'])
            . '/';
        header('Location: ' . $url);
        exit(0);
    } else if ($path == '/') {
        return 'www/index.php';
    }

    $path = rewriteWithHtaccess($path);

    if (substr($path, -4) == '.css'
        || substr($path, -3) == '.js'
        || substr($path, 0, 9) == '/phorkie/'
    ) {
        header('Expires: ' . date('r', time() + 86400 * 7));
    }
    return 'www' . $path;
}

function rewriteWithHtaccess($path)
{
    //remove the leading slash /
    $cpath = substr($path, 1);
    $bFoundMatch = false;
    $map = include('phar://' . __FILE__ . '/src/gen-rewritemap.php');
    foreach ($map as $pattern => $replace) {
        if (preg_match($pattern, $cpath, $matches)) {
            $bFoundMatch = true;
            break;
        }
    }
    if (!$bFoundMatch) {
        return $path;
    }
    $newcpath = preg_replace($pattern, $replace, $cpath);
    if (strpos($newcpath, '?') === false) {
        return '/' . $newcpath;
    }
    list($cfile, $getParams) = explode('?', $newcpath, 2);
    if ($getParams != '') {
        parse_str($getParams, $_GET);
    }
    return '/' . $cfile;
}

//Phar::interceptFileFuncs();
set_include_path(
    'phar://' . __FILE__
    . PATH_SEPARATOR . 'phar://' . __FILE__ . '/lib/'
);
Phar::webPhar(null, $web, null, array(), 'rewritePath');

//TODO: implement CLI script runner
echo "phorkie can only be used in the browser\n";
exit(1);
__HALT_COMPILER();
?>
