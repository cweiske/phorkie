<?php
namespace phorkie;

class Forker
{
    public function forkLocal($repo)
    {
        $new = $this->fork($repo->gitDir);
        \copy($repo->gitDir . '/description', $new->gitDir . '/description');
        $this->index($new);
        return $new;
    }

    public function forkRemote($cloneUrl, $originalUrl)
    {
        $new = $this->fork($cloneUrl);
        file_put_contents(
            $new->gitDir . '/description',
            'Fork of ' . $originalUrl
        );
        $this->index($new);
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

        foreach (\glob($new->gitDir . '/hooks/*') as $hookfile) {
            \unlink($hookfile);
        }

        return $new;
    }

    protected function index($repo)
    {
        $db = new Database();
        $db->getIndexer()->addRepo($repo);
    }
}

?>
