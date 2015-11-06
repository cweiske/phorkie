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
     * Get the filename usable as HTML anchor.
     *
     * @return string
     */
    function getAnchorName()
    {
        return str_replace(' ', '-', $this->getFilename());
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
        return strtolower(substr($this->path, strrpos($this->path, '.') + 1));
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
        $cache = new Renderer_Cache();
        return $cache->toHtml($this, $res);
    }

    /**
     * Get a link to the file
     *
     * @param string $type   Link type. Supported are:
     *                       - "display"
     *                       - "raw"
     *                       - "tool"
     * @param string $option Additional option, e.g. tool name
     * @param boolean $full   Return full URL or normal relative
     *
     * @return string
     */
    public function getLink($type, $option = null, $full = false)
    {
        if ($type == 'raw') {
            if ($this->repo->hash === null) {
                $link = $this->repo->id . '/raw/' . $this->getFilename();
            } else {
                $link = $this->repo->id . '/rev-raw/' . $this->repo->hash
                    . '/' . $this->getFilename();
            }
        } else if ($type == 'tool') {
            $link = $this->repo->id
                . '/tool/' . $option
                . '/' . $this->getFilename();
        } else if ($type == 'display') {
            $link = $this->repo->id . '#' . $this->getFilename();
        } else {
            throw new Exception('Unknown type');
        }

        if ($full) {
            $link = Tools::fullUrl($link);
        }
        return $link;
    }

    /**
     * @return string Mime type of file, NULL if no type detected
     */
    public function getMimeType()
    {
        $ext = $this->getExt();
        if (isset($GLOBALS['phorkie']['languages'][$ext])) {
            return $GLOBALS['phorkie']['languages'][$ext]['mime'];
        }

        $mte = new \MIME_Type_Extension();
        $type = $mte->getMIMEType($this->getFilename());
        if (!\PEAR::isError($type)) {
            return $type;
        }
        return null;
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
            return $this->isNonBinary();
        }

        $type = $this->getMimeType();
        if ($type === null) {
            return $this->isNonBinary();
        }
        return substr($type, 0, 5) === 'text/'
            || $type == 'application/javascript'
            || substr($type, -4) == '+xml'
            || substr($type, -5) == '+json';
    }

    /**
     * Look at the file's bytes and guess if it's binary or not.
     *
     * @return boolean True if it's most likely plain text
     */
    public function isNonBinary()
    {
        $fp = fopen($this->getFullPath(), 'r');
        if (!$fp) {
            return false;
        }

        //When multibyte extension is not installed,
        // we only allow files with ASCII characters.
        // Files with UTF-8 characters will not be detected as text.
        $hasMb = function_exists('mb_detect_encoding');

        $pos = 0;
        $data = '';
        while (false !== ($char = fgetc($fp)) && ++$pos < 100) {
            $data .= $char;
            if (!$hasMb && ord($char) > 128) {
                fclose($fp);
                return false;
            }
        }
        fclose($fp);

        if (!$hasMb) {
            return true;
        }

        if (mb_detect_encoding($data) === false) {
            return false;
        }
        return true;
    }
}

?>
