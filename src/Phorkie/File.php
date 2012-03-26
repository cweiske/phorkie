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

    public static $arMimeTypeMap = array(
        'css'  => 'text/css',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'js'   => 'application/javascript',
        'php'  => 'text/x-php',
        'txt'  => 'text/plain',
    );

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

    public function getHighlightedContent()
    {
        /**
         * Yes, geshi needs to be in your include path
         * We use the mediawiki geshi extension package.
         */
        require 'MediaWiki/geshi/geshi/geshi.php';
        $geshi = new \GeSHi($this->getContent(), $this->getType());
        $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
        $geshi->set_header_type(GESHI_HEADER_DIV);
        return $geshi->parse_code();
    }

    public function getMimeType()
    {
        $type = $this->getType();
        if (!isset(static::$arMimeTypeMap[$type])) {
            return null;
        }
        return static::$arMimeTypeMap[$type];
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