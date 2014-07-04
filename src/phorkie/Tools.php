<?php
namespace phorkie;


class Tools
{
    /**
     * Delete an entire directory structure
     *
     * @param string $path Path to delete
     *
     * @return bool
     */
    public static function recursiveDelete($path)
    {
        if (!file_exists($path)) {
            return true;
        }
        if (!is_dir($path) || is_link($path)) {
            return unlink($path);
        }
        foreach (scandir($path) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $filepath = $path . DIRECTORY_SEPARATOR . $file;
            if (!static::recursiveDelete($filepath)) {
                return false;
            };
        }
        return rmdir($path);
    }

    /**
     * Create a full URL with protocol and host name
     *
     * @param string $path Path to the file, with leading /
     *
     * @return string Full URL
     */
    public static function fullUrl($path = '')
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $prot = 'https';
        } else {
            $prot = 'http';
        }
        return $prot . '://' . $_SERVER['HTTP_HOST'] . $GLOBALS['phorkie']['cfg']['baseurl'] . $path;
    }

    /**
     * Removes malicious parts from a file name
     *
     * @param string $file File name from the user
     *
     * @return string Fixed and probably secure filename
     */
    public static function sanitizeFilename($file)
    {
        $file = trim($file);
        $file = str_replace(array('\\', '//'), '/', $file);
        $file = str_replace('/../', '/', $file);
        if (substr($file, 0, 3) == '../') {
            $file = substr($file, 3);
        }
        if (substr($file, 0, 1) == '../') {
            $file = substr($file, 1);
        }

        return $file;
    }


    public static function detectBaseUrl()
    {
        if (!isset($_SERVER['REQUEST_URI'])
            || !isset($_SERVER['SCRIPT_NAME'])
        ) {
            return '/';
        }

        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];
        if (substr($scriptName, -4) != '.php') {
            //a phar
            return $scriptName . '/';
        }

        if (substr($requestUri, -4) != '.php') {
            $requestUri .= '.php';
        }
        $snl = strlen($scriptName);
        if (substr($requestUri, -$snl) == $scriptName) {
            return substr($requestUri, 0, -$snl) . '/';
        }

        return '/';
    }

    /**
     * Resolves "/../" and "/./" in file paths without validating them.
     */
    public static function foldPath($path)
    {
        $path = str_replace('/./', '/', $path);
        $path = str_replace('/./', '/', $path);
        $path = preg_replace('#/[^/]+/\.\./#', '/', $path);
        $path = preg_replace('#/[^/]+/\.\./#', '/', $path);
        return $path;
    }
}
?>
