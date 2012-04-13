<?php
namespace phorkie;

class Tool_Info
{
    public $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getLink(File $file)
    {
        return $file->getLink('tool', $this->stripPrefix($this->class));
    }

    public function getTitle()
    {
        return $this->stripPrefix($this->class);
    }

    protected function stripPrefix($class)
    {
        $prefix = '\\phorkie\\Tool_';
        if (substr($class, 0, strlen($prefix)) === $prefix) {
            return substr($class, strlen($prefix));
        }
        return $class;
    }
}

?>
