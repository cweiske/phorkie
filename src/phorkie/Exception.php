<?php
namespace phorkie;

class Exception extends \Exception
{
    public $httpStatusCode = 500;
}

?>
