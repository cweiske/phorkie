<?php
namespace phorkie;

class Renderer_Geshi
{
    /**
     * Converts the code to HTML
     *
     * @param File $file File to render
     *
     * @return string HTML
     */
    public function toHtml(File $file)
    {
        /**
         * Yes, geshi needs to be in your include path
         * We use the mediawiki geshi extension package.
         */
        require_once 'MediaWiki/geshi/geshi/geshi.php';
        $geshi = new \GeSHi($file->getContent(), $this->getType($file));
        $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
        $geshi->set_header_type(GESHI_HEADER_DIV);
        return $geshi->parse_code();
    }

    /**
     * Returns the type of the file, as used by Geshi
     *
     * @return string
     */
    public function getType($file)
    {
        $ext = $file->getExt();
        if (isset($GLOBALS['phorkie']['languages'][$ext]['geshi'])) {
            $ext = $GLOBALS['phorkie']['languages'][$ext]['geshi'];
        }

        return $ext;
    }

}

?>
