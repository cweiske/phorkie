<?php
namespace phorkie;

class SetupCheck
{
    protected $deps = array(
        'pear.php.net/VersionControl_Git'  => 'VersionControl_Git',
        'pear.twig-project.org/Twig'       => 'Twig_Autoloader',
        'pear.php.net/Date_HumanDiff'      => 'Date_HumanDiff',
        'pear.php.net/HTTP_Request2'       => 'HTTP_Request2',
        'pear.php.net/OpenID'              => 'OpenID',
        'pear.php.net/Pager'               => 'Pager',
        'pear.php.net/Services_Libravatar' => 'Services_Libravatar',
        'pear2.php.net/PEAR2_Services_Linkback'  => '\\PEAR2\\Services\\Linkback\\Client',
        'zustellzentrum.cweiske.de/MIME_Type_PlainDetect' => 'MIME_Type_PlainDetect',
    );

    protected $writableDirs;
    protected $elasticsearch;

    public function __construct()
    {
        $cfg = $GLOBALS['phorkie']['cfg'];
        $this->writableDirs = array(
            'gitdir' => $cfg['gitdir'],
            'workdir' => $cfg['workdir'],
        );
        $this->elasticsearch = $cfg['elasticsearch'];
    }

    public static function run()
    {
        $sc = new self();
        $sc->checkDeps();
        $sc->checkDirs();
        $sc->checkGit();
        $sc->checkDatabase();
        $sc->checkMimeTypeDetection();
    }

    public function checkDeps()
    {
        foreach ($this->deps as $package => $class) {
            if (!class_exists($class, true)) {
                $this->fail('PEAR package not installed: ' . $package);
            }
        }

        $geshi = stream_resolve_include_path(
            $GLOBALS['phorkie']['cfg']['geshi']
        );
        if ($geshi === false) {
            $this->fail('GeSHi not available');
        }

        $markdown = stream_resolve_include_path('markdown.php');
        if ($markdown === false) {
            $this->fail('Markdown renderer not available');
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
        if (!preg_match('#^git version ([0-9.]+(rc[0-9]+)?)$#', $line, $matches)) {
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
        if ($this->elasticsearch == '') {
            return;
        }

        $es = parse_url($this->elasticsearch);
        if (!preg_match("#/.+/#", $es['path'], $matches)) {
            $this->fail(
                'Improper elasticsearch url.  Elasticsearch requires a'
                . ' search domain to store your data.'
                . ' (e.g. http://localhost:9200/phorkie/)'
            );
        }
        $dbs = new Database();
        $dbs->getSetup()->setup();
    }

    public function checkMimeTypeDetection()
    {
        $rp = new Repository_Post();
        if ($rp->getType('<?php echo "foo"; ?>') != 'php') {
            $this->fail('MIME type detection fails');
        }
    }

    public function fail($msg)
    {
        throw new Exception($msg);
    }
}

?>
