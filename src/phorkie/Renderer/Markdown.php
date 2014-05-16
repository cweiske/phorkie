<?php
namespace phorkie;

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
        if (class_exists('\\Michelf\\Markdown', true)) {
            //composer-installed version 1.4+
            $markdown = \Michelf\Markdown::defaultTransform(
                $file->getContent()
            );
        } else {
            //PEAR-installed version 1.0.2 has a different API
            require_once 'markdown.php';
            $markdown = \markdown($file->getContent());
        }

        return '<div class="markdown">'
            . $markdown
            . '</div>';
    }
}

?>
