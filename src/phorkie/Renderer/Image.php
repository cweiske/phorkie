<?php
namespace phorkie;

class Renderer_Image
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
        return '<div class="image">'
            . '<img'
            . ' src="' . htmlspecialchars($file->getLink('raw')) . '"'
            . ' alt="' . htmlspecialchars($file->getFilename()) . '"'
            . '/>'
            . '</div>';
    }
}
?>
