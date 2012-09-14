<?php
$GLOBALS['phorkie']['cfg'] = array(
    'debug'         => false,
    'gitdir'        => __DIR__ . '/../repos/git/',
    'workdir'       => __DIR__ . '/../repos/work/',
    'tpl'           => __DIR__ . '/templates/',
    'css'           => 'http://twitter.github.com/bootstrap/assets/css/bootstrap.css',
    'title'         => 'phorkie',
    'topbar'        => '',
    'setupcheck'    => true,
    'elasticsearch' => null,
    'geshi'         => 'MediaWiki/geshi/geshi/geshi.php',
    'index'         => 'new'//"new" or "list"
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
    'xml' => array(
        'title' => 'XML',
        'mime'  => 'text/xml',
        'geshi' => 'xml'
    ),
);
?>
