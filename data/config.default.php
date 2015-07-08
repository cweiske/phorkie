<?php
$pharFile = \Phar::running();
if ($pharFile == '') {
    $phorkieDir = __DIR__ . '/../';
    $wwwDir = $phorkieDir . 'www/';
} else {
    //remove phar:// from the path
    $phorkieDir = dirname(substr($pharFile, 7)) . '/';
    $wwwDir = $phorkieDir;
}

$GLOBALS['phorkie']['cfg'] = array(
    'debug'         => false,
    'git'           => array(
        'public'    => '%BASEURL%' . 'repos/git/',
        'private'   => null,
    ),
    'cachedir'      => $phorkieDir . 'cache/',
    'gitdir'        => $wwwDir . 'repos/git/',
    'workdir'       => $wwwDir . 'repos/work/',
    'tpl'           => __DIR__ . '/templates/',
    'baseurl'       => null,
    'avatars'       => true,
    'css'           => '',
    'iconpng'       => '',//phorkie browser icon (favicon)
    'title'         => 'phorkie',
    'topbar'        => '',
    'setupcheck'    => true,
    'elasticsearch' => null,
    'index'         => 'new',//"new" or "list"
    'perPage'       => 10,
    'defaultListPage' => 'last',//a number or "last"
    'notificator'   => array(
        /* send out pingback/webmentions when a remote paste is forked */
        'linkback'  => true,
        'webhook'   => array(
            /* array of urls that get called when
               a paste is created, edited or deleted */
        )
    ),
);
$GLOBALS['phorkie']['auth'] = array(
    // 0 = public, no authentication, 1 = protect adds/edits/deletes,
    // 2 = require authentication
    'securityLevel'   => 0,
    'listedUsersOnly' => false,
    'users'           => array(),             // Array of OpenIDs that may login
    'anonymousName'   => 'Anonymous',         // Email for non-authenticated commits
    'anonymousEmail'  => 'anonymous@phorkie', // Email for non-authenticated commits
);
$GLOBALS['phorkie']['tools'] = array(
    '\\phorkie\\Tool_Xmllint' => true,
    '\\phorkie\\Tool_PHPlint' => true,
);
/**
 * Array of supported file types / languages.
 * Key is the file extension
 */
$GLOBALS['phorkie']['languages'] = array(
    'conf' => array(
        'title' => 'Configuration',
        'mime'  => 'text/ini',
        'geshi' => 'ini',
        'show'  => false
    ),
    'css' => array(
        'title' => 'CSS',
        'mime'  => 'text/css',
        'geshi' => 'css'
    ),
    'diff' => array(
        'title' => 'Diff',
        'mime'  => 'text/diff',
        'geshi' => 'diff'
    ),
    'htm' => array(
        'title' => 'HTML',
        'mime'  => 'text/html',
        'geshi' => 'xml'
    ),
    'html' => array(
        'title' => 'HTML',
        'mime'  => 'text/html',
        'geshi' => 'xml',
        'show'  => false
    ),
    'jpg' => array(
        'title' => 'JPEG image',
        'mime'  => 'image/jpeg',
        'show'  => false
    ),
    'ini' => array(
        'title' => 'Ini',
        'mime'  => 'text/ini',
        'geshi' => 'ini'
    ),
    'js' => array(
        'title' => 'Javascript',
        'mime'  => 'application/javascript',
        'geshi' => 'javascript'
    ),
    'json' => array(
        'title' => 'Javascript',
        'mime'  => 'application/javascript',
        'geshi' => 'javascript',
        'show'  => false
    ),
    'md' => array(
        'title' => 'Markdown',
        'mime'  => 'text/x-markdown',
        'renderer' => '\\phorkie\\Renderer_Markdown'
    ),
    'pl' => array(
        'title' => 'Perl',
        'mime'  => 'application/x-perl',
        'geshi' => 'pl'
    ),
    'php' => array(
        'title' => 'PHP',
        'mime'  => 'text/x-php',
        'geshi' => 'php'
    ),
    'png' => array(
        'title' => 'PNG image',
        'mime'  => 'image/png',
        'show'  => false
    ),
    'rb' => array(
        'title' => 'Ruby/Rails',
        'mime'  => 'text/x-ruby', /* Is this an acceptable mime type? */
        'geshi' => 'rails'
    ),
    'rst' => array(
        'title' => 'reStructuredText',
        'mime'  => 'text/x-rst',
        'geshi' => 'rst',
        'renderer' => '\\phorkie\\Renderer_ReStructuredText',
    ),
    'sh' => array(
        'title' => 'Shell script (Bash)',
        'mime'  => 'text/x-shellscript',
        'geshi' => 'bash'
    ),
    'sql' => array(
        'title' => 'SQL',
        'mime'  => 'text/x-sql',
        'geshi' => 'sql'
    ),
    'svg' => array(
        'title' => 'SVG image',
        'mime'  => 'image/svg+xml',
        'show'  => false
    ),
    'txt' => array(
        'title' => 'Text (plain)',
        'mime'  => 'text/plain',
        'geshi' => 'txt'
    ),
    'ts' => array(
        'title' => 'TypoScript',
        'mime'  => 'text/x-typoscript',/* TODO: correct type */
        'geshi' => 'typoscript'
    ),
    'wsdl' => array(
        'title' => 'WSDL',
        'mime'  => 'application/wsdl+xml',
        'geshi' => 'xml'
    ),
    'xml' => array(
        'title' => 'XML',
        'mime'  => 'text/xml',
        'geshi' => 'xml'
    ),
    'xsl' => array(
        'title' => 'eXtensible Stylesheet Language',
        'mime'  => 'text/xml',
        'geshi' => 'xml',
        'show'  => false
    ),
);

//needed for UTF-8 characters in file names
setlocale(LC_CTYPE, 'en_US.UTF_8');
?>
