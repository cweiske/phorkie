<?php
namespace phorkie;

class GitCommandBinary extends \VersionControl_Git_Util_Command
{
    /**
     * Do not strip anything, we're accessing binary files
     */
    public function stripEscapeSequence($string)
    {
        return $string;
    }
}

?>