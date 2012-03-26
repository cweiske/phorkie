<?php
namespace Phorkie;

class File
{
    /**
     * Full path to the file
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

    public function __construct($path, Repository $repo)
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
        return basename($this->path);
    }

    /**
     * Returns the type of the file, as used internally by Phorkie
     *
     * @return string
     */
    public function getType()
    {
        return substr($this->path, strrpos($this->path, '.') + 1);
    }

    public function getContent()
    {
        return file_get_contents($this->path);
    }

    /**
     * Get a link to the file
     *
     * @param string $type Link type. Supported are:
     *                     - "raw"
     *                     - "display"
     *
     * @return string
     */
    public function getLink($type)
    {
        if ($type == 'raw') {
            return '/' . $this->repo->id . '/raw/' . $this->getFilename();
        }
        throw new Exception('Unknown type');
    }
}

?>