<?php
namespace phorkie;

class Forker
{
    public function forkLocal($repo)
    {
        $new = $this->fork($repo->gitDir);

        \copy($repo->gitDir . '/description', $new->gitDir . '/description');
        $new->getVc()
            ->getCommand('config')
            ->addArgument('remote.origin.title')
            ->addArgument(file_get_contents($repo->gitDir . '/description'))
            ->execute();

        $this->index($new);

        $not = new Notificator();
        $not->create($new);

        return $new;
    }

    public function forkRemote($cloneUrl, $originalUrl, $title = null)
    {
        $new = $this->fork($cloneUrl);

        $new->getVc()
            ->getCommand('config')
            ->addArgument('remote.origin.title')
            ->addArgument($title)
            ->execute();
        if ($originalUrl != $cloneUrl) {
            $new->getVc()
                ->getCommand('config')
                ->addArgument('remote.origin.homepage')
                ->addArgument($originalUrl)
                ->execute();
        }

        if ($title === null) {
            $title = 'Fork of ' . $originalUrl;
        }
        file_put_contents($new->gitDir . '/description', $title);

        $this->index($new);

        $not = new Notificator();
        $not->create($new);

        return $new;
    }


    protected function fork($pathOrUrl)
    {
        $rs = new Repositories();
        $new = $rs->createNew();
        $vc = $new->getVc();

        //VersionControl_Git wants an existing dir, git clone not
        \rmdir($new->gitDir);

        $cmd = $vc->getCommand('clone')
            //this should be setOption, but it fails with a = between name and value
            ->addArgument('--separate-git-dir')
            ->addArgument(
                $GLOBALS['phorkie']['cfg']['gitdir'] . '/' . $new->id . '.git'
            )
            ->addArgument($pathOrUrl)
            ->addArgument($new->workDir);
        try {
            $cmd->execute();
        } catch (\Exception $e) {
            //clean up, we've got no workdir otherwise
            $new->delete();
            throw $e;
        }

        $rs = new Repository_Setup($new);
        $rs->afterInit();

        return $new;
    }

    protected function index($repo)
    {
        $db = new Database();
        $db->getIndexer()->addRepo($repo);
    }
}

?>
