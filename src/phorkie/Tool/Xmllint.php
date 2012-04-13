<?php
namespace phorkie;

class Tool_Xmllint
{
    public static $arSupportedExtensions = array(
        'htm', 'html', 'xml'
    );

    public function run(File $file)
    {
        $fpath = $file->getPath();
        $fpathlen = strlen($fpath);

        $res = new Tool_Result();
        $cmd = 'xmllint --noout ' . escapeshellarg($fpath) . ' 2>&1';
        exec($cmd, $output, $retval);
        if ($retval == 0) {
            $res->annotations['general'][] = new Tool_Result_Line(
                'XML is well-formed', 'ok'
            );
            return $res;
        }

        for ($i = 0; $i < count($output); $i += 3) {
            $line = $output[$i];
            if (substr($line, 0, $fpathlen) != $fpath) {
                throw new Exception('xmllint does not behave as expected: ' . $line);
            }
            list($line, $msg) = explode(':', substr($line, $fpathlen + 1), 2);
            $res->annotations[$line][] = new Tool_Result_Line(
                $msg, 'error'
            );
        }

        $res->annotations['general'][] = new Tool_Result_Line(
            'XML is not well-formed', 'error'
        );

        return $res;
    }
}

?>