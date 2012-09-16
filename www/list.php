<?php
/**
 * List a repository
 */
namespace phorkie;
require_once 'www-header.php';
if ($GLOBALS['phorkie']['auth']['secure'] == 2) {
    require_once 'secure.php';
}
$rs = new Repositories();

$page = 0;
if (isset($_GET['page'])) {
    if (!is_numeric($_GET['page'])) {
        throw new Exception_Input('List page is not numeric');
    }
    $page = (int)$_GET['page'] - 1;
}

$perPage = 10;
list($repos, $repoCount) = $rs->getList($page, $perPage);

$pager = new Html_Pager(
    $repoCount, $perPage, $page + 1, '/list/%d'
);

render(
    'list',
    array(
        'repos' => $repos,
        'pager' => $pager,
    )
);
?>
