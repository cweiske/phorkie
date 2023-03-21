<?php
namespace phorkie;

class Tool_MIME_Type_PlainDetect extends \MIME_Type_PlainDetect
{
    /**
     * Try to find the magic file copied by phing's build.xml into
     * lib/data/.
     *
     * @return string path to the magic file
     */
    public static function getMagicFile()
    {
        $rootdir = __DIR__ . '/../../../../../';
        $magicPath = $rootdir . '/lib/cweiske/mime_type_plaindetect/data/programming.magic';
        if (!\Phar::running()) {
            return $magicPath;
        } else {
            //magic file within a .phar does not work:
            // https://bugs.php.net/bug.php?id=67556
            //put it outside
            $target = '/tmp/phorkie-programming.magic';
            if (!file_exists($target)) {
                copy($magicPath, $target);
            }
            return $target;
        }
        return parent::getMagicFile();
    }
}
?>
