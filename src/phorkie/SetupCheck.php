<?php
namespace phorkie;

class SetupCheck
{
    protected $deps = array(
        'pear.php.net/VersionControl_Git' => 'VersionControl_Git',
        'pear.twig-project.org/Twig'      => 'Twig_Autoloader',
        'pear.php.net/Date_HumanDiff'     => 'Date_HumanDiff',
    );

    protected $writableDirs;


    public function __construct()
    {
        $cfg = $GLOBALS['phorkie']['cfg'];
        $this->writableDirs = array(
            'gitdir' => $cfg['gitdir'],
            'workdir' => $cfg['workdir'],
        );
    }

    public static function run()
    {
        $sc = new self();
        $sc->checkDeps();
        $sc->checkDirs();
    }

    public function checkDeps()
    {
        foreach ($this->deps as $package => $class) {
            if (!class_exists($class, true)) {
                $this->fail('PEAR package not installed: ' . $package);
            }
        }
    }

    public function checkDirs()
    {
        foreach ($this->writableDirs as $name => $dir) {
            if (!is_dir($dir)) {
                $this->fail($name . ' directory does not exist at ' . $dir);
            }
            if (!is_writable($dir)) {
                $this->fail($name . ' directory is not writable at ' . $dir);
            }
        }
    }

    public function fail($msg)
    {
        throw new Exception($msg);
    }
}

?>
