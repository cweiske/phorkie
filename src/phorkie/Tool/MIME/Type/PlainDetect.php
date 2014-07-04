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
        if (file_exists($rootdir . '/lib/PEAR.php')) {
            if (!\Phar::running()) {
                return $rootdir . '/lib/data/programming.magic';
            } else {
                //magic file within a .phar does not work:
                // https://bugs.php.net/bug.php?id=67556
                //put it outside
                $target = '/tmp/phorkie-programming.magic';
                if (!file_exists($target)) {
                    copy(
                        $rootdir . '/lib/data/programming.magic',
                        $target
                    );
                }
                return $target;
            }
        }
        return parent::getMagicFile();
    }
}
?>
