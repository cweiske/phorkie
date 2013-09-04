<?php
namespace phorkie;

class File
{
    /**
     * Path to the file, relative to repository work directory
     *
     * @var string
     */
    public $path;

    /**
     * Repository this file belongs to
     *
     * @var string
     */
    public $repo;

    /**
     * Commit revision this file is at
     */
    public $hash;

    public function __construct($path, Repository $repo = null)
    {
        $this->path = $path;
        $this->repo = $repo;
    }

    /**
     * Get filename relative to the repository path
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->path;
    }

    /**
     * Return the full path to the file
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->repo->workDir . '/' . $this->path;
    }

    /**
     * Get file extension without dot
     *
     * @return string
     */
    public function getExt()
    {
        return substr($this->path, strrpos($this->path, '.') + 1);
    }

    public function getContent()
    {
        if ($this->repo->hash) {
            //quick hack until https://pear.php.net/bugs/bug.php?id=19385 is fixed
            $cmd = new GitCommandBinary($this->repo->getVc());
            $cmd->setSubCommand('show');
            return $cmd
                ->addArgument($this->repo->hash . ':' . $this->path)
                ->execute();
        }

        return file_get_contents($this->getFullPath());
    }

    public function getRenderedContent(Tool_Result $res = null)
    {
        $ext   = $this->getExt();
        $class = '\\phorkie\\Renderer_Unknown';

        if (isset($GLOBALS['phorkie']['languages'][$ext]['renderer'])) {
            $class = $GLOBALS['phorkie']['languages'][$ext]['renderer'];
        } else if ($this->isText()) {
            $class = '\\phorkie\\Renderer_Geshi';
        } else if (isset($GLOBALS['phorkie']['languages'][$ext]['mime'])) {
            $type = $GLOBALS['phorkie']['languages'][$ext]['mime'];
            if (substr($type, 0, 6) == 'image/') {
                $class = '\\phorkie\\Renderer_Image';
            }
        }

        $rend = new $class();
        return $rend->toHtml($this, $res);
    }

    /**
     * Get a link to the file
     *
     * @param string $type   Link type. Supported are:
     *                       - "raw"
     *                       - "tool"
     * @param string $option Additional option, e.g. tool name
     *
     * @return string
     */
    public function getLink($type, $option = null)
    {
        if ($type == 'raw') {
            if ($this->repo->hash === null) {
                return $this->repo->id . '/raw/' . $this->getFilename();
            } else {
                return $this->repo->id . '/rev-raw/' . $this->repo->hash
                    . '/' . $this->getFilename();
            }
        } else if ($type == 'tool') {
            return $this->repo->id
                . '/tool/' . $option
                . '/' . $this->getFilename();
        }
        throw new Exception('Unknown type');
    }

    /**
     * @return string Mime type of file
     */
    public function getMimeType()
    {
        $ext = $this->getExt();
        if (!isset($GLOBALS['phorkie']['languages'][$ext])) {
            return null;
        }
        return $GLOBALS['phorkie']['languages'][$ext]['mime'];
    }

    /**
     * @return array Array of Tool_Info objects
     */
    public function getToolInfos()
    {
        if ($this->repo->hash !== null) {
            return array();
        }

        $tm = new Tool_Manager();
        return $tm->getSuitable($this);
    }

    /**
     * Tells if the file contains textual content and is editable.
     *
     * @return boolean
     */
    public function isText()
    {
        $ext = $this->getExt();
        if ($ext == '') {
            //no file extension? then consider the size
            $size = filesize($this->getFullPath());
            //files <= 4kiB are considered to be text
            return $size <= 4096;
        }

        if (!isset($GLOBALS['phorkie']['languages'][$ext]['mime'])) {
            return false;
        }

        $type = $GLOBALS['phorkie']['languages'][$ext]['mime'];
        return substr($type, 0, 5) === 'text/'
            || $type == 'application/javascript'
            || substr($type, -4) == '+xml'
            || substr($type, -5) == '+json';
    }
}

?>
