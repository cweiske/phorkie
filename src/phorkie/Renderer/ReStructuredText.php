<?php
namespace phorkie;

/**
 * Requires cli program "rst2html" (python docutils) to be installed
 */
class Renderer_ReStructuredText
{
    /**
     * Converts the rST to HTML
     *
     * @param File $file File to render
     *
     * @return string HTML
     */
    public function toHtml(File $file)
    {
        $descriptorspec = array(
            0 => array('pipe', 'r'),//stdin
            1 => array('pipe', 'w'),//stdout
            2 => array('pipe', 'w') //stderr
        );
        $process = proc_open('rst2html', $descriptorspec, $pipes);
        if (!is_resource($process)) {
            return '<div class="alert alert-error">'
                . 'Cannot open process to execute rst2html'
                . '</div>';
        }

        fwrite($pipes[0], $file->getContent());
        fclose($pipes[0]);

        $html = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $retval = proc_close($process);

        //cheap extraction of the rst html body
        $html = substr($html, strpos($html, '<body>') + 6);
        $html = substr($html, 0, strpos($html, '</body>'));

        if ($retval != 0) {
            $html = '<div class="alert">'
                . 'rst2html encountered some error; return value ' . $retval . '<br/>'
                . 'Error message: ' . $errors
                . '</div>'
                . $html;
        }

        return $html;
    }
}

?>
