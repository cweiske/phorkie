<?php
namespace Phorkie;

class Repositories
{
    public function __construct()
    {
        $this->reposDir = $GLOBALS['phorkie']['cfg']['repos'];
    }

    /**
     * @return Repository
     */
    public function createNew()
    {
        chdir($this->reposDir);
        $dirs = glob('*', GLOB_ONLYDIR);
        sort($dirs, SORT_NUMERIC);
        $n = end($dirs) + 1;
        unset($dirs);

        $dir = $this->reposDir . '/' . $n . '/'; 
        mkdir($dir, 0777);//FIXME
        $r = new Repository();
        $r->id = $n;
        $r->repoDir = $dir;
        return $r;
    }

}

?>
