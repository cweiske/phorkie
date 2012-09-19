<?php
namespace phorkie;

class SetupCheck
{
    protected $deps = array(
        'pear.php.net/VersionControl_Git'  => 'VersionControl_Git',
        'pear.twig-project.org/Twig'       => 'Twig_Autoloader',
        'pear.php.net/Date_HumanDiff'      => 'Date_HumanDiff',
        'pear.php.net/HTTP_Request2'       => 'HTTP_Request2',
        'pear.php.net/Pager'               => 'Pager',
        'pear.php.net/Services_Libravatar' => 'Services_Libravatar',
        'zustellzentrum.cweiske.de/MIME_Type_PlainDetect' => 'MIME_Type_PlainDetect',
        'pear.michelf.ca/Markdown'         => 'Markdown',
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
        $sc->checkGit();
        $sc->checkDatabase();
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

    public function checkGit()
    {
        $line = exec('git --version', $lines, $retval);
        if ($retval !== 0) {
            $this->fail('Running git executable failed.');
        }
        if (!preg_match('#^git version ([0-9.]+)$#', $line, $matches)) {
            $this->fail('git version output format unexpected: ' . $line);
        }
        if (version_compare($matches[1], '1.7.5') < 0) {
            $this->fail(
                'git version needs to be at least 1.7.5, got: ' . $matches[1]
            );
        }
    }

    public function checkDatabase()
    {
        $dbs = new Database();
        $dbs->getSetup()->setup();
    }

    public function fail($msg)
    {
        throw new Exception($msg);
    }
}

?>
