<?php
//index repositories in elasticsearch

namespace phorkie;
require_once __DIR__ . '/../src/phorkie/autoload.php';
require_once __DIR__ . '/../data/config.default.php';
if (file_exists(__DIR__ . '/../data/config.php')) {
    require_once __DIR__ . '/../data/config.php';
}
if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    SetupCheck::run();
}


$db = new Database();

if ($db->prefix == '\phorkie\Database_Adapter_Null') {
    echo "Error: No search adapter configured.\n";
    exit(1);
}

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
