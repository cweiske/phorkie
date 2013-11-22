<?php
namespace phorkie;

class Repository_Remote
{
    protected $arConfig;
    protected $name;

    public function __construct($name, $arConfig)
    {
        $this->name = $name;
        $this->arConfig = $arConfig;
    }


    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        if (isset($this->arConfig['title'])) {
            return $this->arConfig['title'];
        }
        if ($this->isLocal()) {
            $local = $this->getLocalRepository();
            if ($local !== null) {
                return $local->getTitle();
            }
            return 'deleted local paste';
        }

        return 'untitled repository';
    }

    public function getCloneURL()
    {
        if ($this->isLocal()) {
            $local = $this->getLocalRepository();
            if ($local !== null) {
                return $local->getCloneURL();
            }
        }

        if (isset($this->arConfig['url'])) {
            return $this->arConfig['url'];
        }
        return null;
    }

    public function getWebURL($full = false)
    {
        if (isset($this->arConfig['homepage'])) {
            return $this->arConfig['homepage'];
        }

        if ($this->isLocal()) {
            $local = $this->getLocalRepository();
            if ($local !== null) {
                return $local->getLink('display', null, $full);
            }
        }

        return null;
    }

    /**
     * Tells you if this remote repository is a paste on the local server
     *
     * @return boolean True of false
     */
    public function isLocal()
    {
        return isset($this->arConfig['url'])
            && $this->arConfig['url']{0} == '/';
    }

    /**
     * If this remote is a local paste, then we'll get the repository object
     * returned
     *
     * @return Repository Repository object or NULL
     */
    public function getLocalRepository()
    {
        if (!file_exists($this->arConfig['url'] . '/config')) {
            return null;
        }
        $dir = basename($this->arConfig['url']);
        if (substr($dir, -4) != '.git') {
            //phorks are bare repositories "123.git"
            return null;
        }
        $repo = new Repository();
        $repo->loadById(substr($dir, 0, -4));
        return $repo;
    }

}

?>
