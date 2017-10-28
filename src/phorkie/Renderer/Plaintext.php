<?php
namespace phorkie;

/**
 * Render plain .txt files
 */
class Renderer_Plaintext
{
    /**
     * Simply displays the file
     *
     * @param File $file File to render
     *
     * @return string HTML
     */
    public function toHtml(File $file)
    {
        $html = '<div class="code"><pre class="txt">'
            . htmlspecialchars($file->getContent())
            . '</pre></div>' . "\n";
        return $html;
    }
}

?>
