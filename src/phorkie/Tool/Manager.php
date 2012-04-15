<?php
namespace phorkie;


class Tool_Manager
{
    public function getSuitable(File $file)
    {
        $ext = $file->getExt();
        $suitables = array();
        foreach ($GLOBALS['phorkie']['tools'] as $class => $arSetup) {
            if (array_search($ext, $class::$arSupportedExtensions) !== false) {
                $suitables[] = new Tool_Info($class);
            }
        }
        return $suitables;
    }

    /**
     * Returns the class name from a tool name
     *
     * @param string $name Full class name or short name without
     *                     'phorkie\\Tool_' prefix
     *
     * @return string Class name or NULL if not found
     */
    public function getClass($name)
    {
        if (strpos($name, '\\') === false && strpos($name, '_') === false) {
            return '\\phorkie\\Tool_' . $name;
        }
        return $name;
    }

    public function loadTool($name)
    {
        $class = $this->getClass($name);
        if (!class_exists($class, true)) {
            throw new Exception('Tool does not exist: ' . $class);
        }
        
        return new $class();
    }
}

?>