<?php
namespace phorkie;

class Repository_Setup
{
    protected $repo;

    public function __construct(Repository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Should be called right after a repository has been created,
     * either by "git init" or "git clone".
     * Takes care of removing hook example files and creating
     * the git daemon export file
     *
     * @return void
     */
    public function afterInit()
    {
        foreach (glob($this->repo->gitDir . '/hooks/*') as $hookfile) {
            unlink($hookfile);
        }
        touch($this->repo->gitDir . '/git-daemon-export-ok');
    }

}

?>
