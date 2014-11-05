<?php
namespace phorkie;

class Repository_ConnectionInfo
{
    protected $arConfig;
    protected $repo;


    public function __construct(Repository $repo)
    {
        $this->repo = $repo;
        //we need raw parsing; https://bugs.php.net/bug.php?id=68347
        $this->arConfig = parse_ini_file(
            $this->repo->gitDir . '/config', true, INI_SCANNER_RAW
        );
    }

    public function isFork()
    {
        return $this->getOrigin() !== null;
    }

    public function hasForks()
    {
        return count($this->getForks()) > 0;
    }


    public function getOrigin()
    {
        return $this->getRemote('origin');
    }

    /**
     * @return Repository_Remote|null NULL if the remote does not exist, array
     *                                with repository information otherwise
     */
    public function getRemote($name)
    {
        if (!isset($this->arConfig['remote "' . $name . '"'])) {
            return null;
        }
        return new Repository_Remote($name, $this->arConfig['remote "' . $name . '"']);
    }

    public function getForks()
    {
        $arForks = array();
        foreach ($this->arConfig as $name => $data) {
            if (substr($name, 0, 13) != 'remote "fork-') {
                continue;
            }
            $arForks[substr($name, 8, -1)] = new Repository_Remote(
                substr($name, 8, -1), $data
            );
        }
        return $arForks;
    }
}
?>
