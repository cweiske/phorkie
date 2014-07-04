<?php
/**
 * Autoloader setup for phorkie
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 */
if (file_exists(__DIR__ . '/../../lib/PEAR.php')) {
    //phing-installed dependencies available ("phing collectdeps")
    set_include_path(
        __DIR__ . '/../'
        . PATH_SEPARATOR . __DIR__ . '/../../lib/'
        . PATH_SEPARATOR . '.'
    );
} else if (file_exists(__DIR__ . '/../../lib/autoload.php')) {
    //composer-installed dependencies available
    set_include_path(
        __DIR__ . '/../'
        . PATH_SEPARATOR . '.'
    );
    require_once __DIR__ . '/../../lib/autoload.php';
} else {
    //use default include path for dependencies
    set_include_path(
        __DIR__ . '/../'
        . PATH_SEPARATOR . get_include_path()
    );
}

spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        if (stream_resolve_include_path($file)) {
            require $file;
        }
    }
);
?>
