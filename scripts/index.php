<?php
//index repositories in elasticsearch

namespace phorkie;
set_include_path(
    __DIR__ . '/../src/'
    . PATH_SEPARATOR . get_include_path()
);
spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        if (stream_resolve_include_path($file)) {
            require $file;
        }
    }
);
require_once __DIR__ . '/../data/config.default.php';
if (file_exists(__DIR__ . '/../data/config.php')) {
    require_once __DIR__ . '/../data/config.php';
}
if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    SetupCheck::run();
}


$db = new Database();
$idx = $db->getIndexer();

//create mapping
echo "Index reset\n";
$db->getSetup()->reset();


$rs = new Repositories();
list($repos, $count) = $rs->getList(0, 10000);
foreach ($repos as $repo) {
    echo 'Indexing ' . $repo->id . "\n";
    $commits = $repo->getHistory();
    $first = count($commits)-1;
    $idx->addRepo($repo, $commits[$first]->committerTime, $commits[0]->committerTime);
}
?>
