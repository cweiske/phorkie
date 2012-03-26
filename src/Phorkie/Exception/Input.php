<?php
namespace Phorkie;

/**
 * Input from e.g. the URL is invalid, like a non-numeric string when one was
 * expected
 */
class Exception_Input extends Exception
{
    public function __construct($msg = '', $code = 0)
    {
        parent::__construct($msg, $code);
        $this->httpStatusCode = 400;
    }
}

?>
