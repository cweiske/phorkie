<?php
namespace phorkie;

/**
 * Something could not be found
 */
class Exception_NotFound extends Exception
{
    public function __construct($msg = '', $code = 0)
    {
        parent::__construct($msg, $code);
        $this->httpStatusCode = 404;
    }
}

?>
