<?php
namespace phorkie;

class Forker
{
    public function forkLocal($repo)
    {
        $new = $this->fork($repo->gitDir);
        \copy($repo->gitDir . '/description', $new->gitDir . '/description');
        return $new;
    }

    public function forkRemote($cloneUrl, $originalUrl)
    {
        $new = $this->fork($cloneUrl);
        file_put_contents(
            $new->gitDir . '/description',
            'Fork of ' . $originalUrl
        );
        return $new;
    }


    protected function fork($pathOrUrl)
    {
        $rs = new Repositories();
        $new = $rs->createNew();
        $vc = $new->getVc();
        \rmdir($new->gitDir);//VersionControl_Git wants an existing dir, git clone not
        $vc->getCommand('clone')
            //this should be setOption, but it fails with a = between name and value
            ->addArgument('--separate-git-dir')
            ->addArgument($GLOBALS['phorkie']['cfg']['gitdir'] . '/' . $new->id . '.git')
            ->addArgument($pathOrUrl)
            ->addArgument($new->workDir)
            ->execute();
        foreach (\glob($new->gitDir . '/hooks/*') as $hookfile) {
            \unlink($hookfile);
        }

        $db = new Database();
        $db->getIndexer()->addRepo($new);

        return $new;
    }
}

?>
