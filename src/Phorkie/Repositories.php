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

    /**
     * Get a list of repository objects
     *
     * @param integer $page    Page number, beginning with 0
     * @param integer $perPage Number of repositories per page
     *
     * @return array Array of Repositories
     */
    public function getList($page = 0, $perPage = 10)
    {
        chdir($this->reposDir);
        $dirs = glob('*', GLOB_ONLYDIR);
        sort($dirs, SORT_NUMERIC);

        $some = array_slice($dirs, $page * $perPage, $perPage);
        $repos = array();
        foreach ($some as $oneDir) {
            $r = new Repository();
            $r->loadById($oneDir);
            $repos[] = $r;
        }
        return $repos;
    }
}

?>
