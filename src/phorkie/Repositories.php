<?php
namespace phorkie;

class Repositories
{
    public function __construct()
    {
        $this->workDir = $GLOBALS['phorkie']['cfg']['workdir'];
        $this->gitDir  = $GLOBALS['phorkie']['cfg']['gitdir'];
    }

    public function createNew(): Repository
    {
        chdir($this->gitDir);
        $dirs = glob('*.git', GLOB_ONLYDIR);
        foreach ($dirs as $key => $dir) {
            $dirs[$key] = substr($dir, 0, -4);
        }
        sort($dirs, SORT_NUMERIC);

        if ($GLOBALS['phorkie']['cfg']['randomIds']) {
            $n = end($dirs) + mt_rand(65536, 16777216);
        } else {
            $n = end($dirs) + 1;
        }

        chdir($this->workDir);
        $dir = $this->workDir . '/' . $n . '/';
        mkdir($dir, fileperms($this->workDir) & 0777);
        $r = new Repository();
        $r->id = $n;
        $r->workDir = $dir;
        $r->gitDir = $this->gitDir . '/' . $n . '.git/';
        mkdir($r->gitDir, fileperms($this->gitDir) & 0777);

        return $r;
    }

    /**
     * Get a list of repository objects
     *
     * @param integer $page    Page number, beginning with 0, or "last"
     * @param integer $perPage Number of repositories per page
     *
     * @return array Array of Repositories first, number of repositories second
     */
    public function getList($page = 0, $perPage = 10): array
    {
        chdir($this->gitDir);
        $dirs = glob('*.git', GLOB_ONLYDIR);
        sort($dirs, SORT_NUMERIC);
        if ($page === 'last') {
            //always show the last 10
            $page = intval(count($dirs) / $perPage);
            $start = count($dirs) - $perPage;
            if ($start < 0) {
                $start = 0;
            }
            $some = array_slice($dirs, $start, $perPage);
        } else {
            $some = array_slice($dirs, $page * $perPage, $perPage);
        }

        $repos = array();
        foreach ($some as $oneDir) {
            $r = new Repository();
            try {
                $r->loadById(substr($oneDir, 0, -4));
            } catch (\VersionControl_Git_Exception $e) {
                if (strpos($e->getMessage(), 'does not have any commits') !== false) {
                    //the git repo is broken as the initial commit
                    // has not been finished
                    continue;
                }
                throw $e;
            }
            $repos[] = $r;
        }
        return array($repos, count($dirs), $page);
    }
}

?>
