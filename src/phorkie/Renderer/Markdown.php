<?php
namespace phorkie;

use Michelf\MarkdownExtra;

class Renderer_Markdown
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
        $markdown = \Michelf\MarkdownExtra::defaultTransform(
            $file->getContent()
        );

        return '<div class="markdown">'
            . $markdown
            . '</div>';
    }
}

?>
