<?php
namespace phorkie;

class Tool_Info
{
    public $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Format the tool path
     *
     * @param File $file
     *
     * @return string
     */
    public function getLink(File $file)
    {
        return $file->getLink('tool', $this->stripPrefix($this->class));
    }

    /**
     * Clean namespace from class
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->stripPrefix($this->class);
    }

    /**
     * Removes custom namespace prefix
     *
     * @param string $class Class of object
     *
     * @return string
     */
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
