<?php
namespace phorkie;

class Tool_PHPlint
{
    public static $arSupportedExtensions = array(
        'php'
    );

    public function run(File $file)
    {
        $fpath = $file->getFullPath();
        $fpathlen = strlen($fpath);

        $res = new Tool_Result();
        $cmd = 'php -l ' . escapeshellarg($fpath) . ' 2>&1';
        exec($cmd, $output, $retval);
        if ($retval == 0) {
            $res->annotations['general'][] = new Tool_Result_Line(
                'No syntax errors detected', 'ok'
            );
            return $res;
        }

        $regex = '#^(.+) in ' . preg_quote($fpath) . ' on line ([0-9]+)$#';
        for ($i = 0; $i < count($output) - 1; $i++) {
            $line = $output[$i];
            if (!preg_match($regex, trim($line), $matches)) {
                throw new Exception('"php -l" does not behave as expected: ' . $line);
            }
            $msg     = $matches[1];
            $linenum = $matches[2];
            $res->annotations[$linenum][] = new Tool_Result_Line(
                $msg, 'error'
            );
        }

        $res->annotations['general'][] = new Tool_Result_Line(
            'PHP code has syntax errors', 'error'
        );

        return $res;
    }
}

?>
