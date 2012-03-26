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
        $n = basename(end(glob($this->reposDir . '/*', GLOB_ONLYDIR))) + 1;
        $dir = $this->reposDir . '/' . $n . '/'; 
        mkdir($dir, 0777);//FIXME
        $r = new Repository();
        $r->id = $n;
        $r->repoDir = $dir;
        return $r;
    }

}

?>
