<?php
namespace phorkie;

class Tool_Result_Line
{
    public $message;
    public $level;

    public function __construct($message, $level = 'ok')
    {
        $this->message = $message;
        $this->setLevel($level);
    }

    public function setLevel($level)
    {
        if ($level !== 'ok' && $level !== 'error' && $level !== 'warning') {
            throw new Exception('Invalid result line level: ' . $level);
        }
        $this->level = $level;
    }

    public function getAlertLevel()
    {
        static $map = array(
            'error'   => 'alert-error',
            'ok'      => 'alert-success',
            'warning' => '',
        );
        return $map[$this->level];
    }
}

?>