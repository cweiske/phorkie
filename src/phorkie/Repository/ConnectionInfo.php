<?php
namespace phorkie;

class Repository_ConnectionInfo
{
    protected $arConfig;
    protected $repo;


    public function __construct(Repository $repo)
    {
        $this->repo = $repo;
        $this->arConfig = parse_ini_file($this->repo->gitDir . '/config', true);
    }

    public function isFork()
    {
        return $this->getOrigin() !== null;
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
        if (!isset($this->arConfig['remote ' . $name])) {
            return null;
        }
        return new Repository_Remote($name, $this->arConfig['remote ' . $name]);
    }

}


?>
