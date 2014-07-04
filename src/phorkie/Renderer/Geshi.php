<?php
namespace phorkie;

class Renderer_Geshi
{
    /**
     * Converts the code to HTML
     *
     * @param File        $file File to render
     * @param Tool_Result $res  Tool result to integrate
     *
     * @return string HTML
     */
    public function toHtml(File $file, Tool_Result $res = null)
    {
        /**
         * Yes, geshi needs to be in your include path
         * We use the geshi pear package.
         */
        if (!class_exists('\\geshi', true)) {
            require_once $GLOBALS['phorkie']['cfg']['geshi'];
        }
        $geshi = new \geshi($file->getContent(), $this->getType($file));
        $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
        $geshi->set_header_type(GESHI_HEADER_DIV);

        if ($res !== null) {
            $geshi->highlight_lines_extra(array_keys($res->annotations));
            $geshi->set_highlight_lines_extra_style('background-color: #F2DEDE');
        }

        return '<div class="code">'
            . str_replace('&nbsp;', '&#160;', $geshi->parse_code())
            . '</div>';
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
