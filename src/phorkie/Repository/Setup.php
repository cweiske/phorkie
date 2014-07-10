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

        $vc = $this->repo->getVc();

        file_put_contents(
            $this->repo->gitDir . '/hooks/post-update',
            <<<CDE
#!/bin/sh
# Hook script to prepare a packed repository for use over dumb transports.

exec git update-server-info

CDE
        );
        chmod($this->repo->gitDir . '/hooks/post-update', 0755);

        //keep track of owner
        $vc->getCommand('config')
            ->addArgument('owner.name')
            ->addArgument($_SESSION['name'])
            ->execute();
        $vc->getCommand('config')
            ->addArgument('owner.email')
            ->addArgument($_SESSION['email'])
            ->execute();
    }

}

?>
