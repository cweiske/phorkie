<?php
namespace phorkie;


class Tools
{
    public static function recursiveDelete($path)
    {
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
    public static function fullUrl($path)
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $prot = 'https';
        } else {
            $prot = 'http';
        }
        return $prot . '://' . $_SERVER['HTTP_HOST'] . $path;
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

}

?>
