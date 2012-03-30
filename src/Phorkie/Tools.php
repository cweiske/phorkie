<?php
namespace Phorkie;


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

}

?>