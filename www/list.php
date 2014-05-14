<?php
/**
 * List a repository
 */
namespace phorkie;
$reqWritePermissions = false;
require_once 'www-header.php';
$rs = new Repositories();

$page = $GLOBALS['phorkie']['cfg']['defaultListPage'];
if (isset($_GET['page'])) {
    if (!is_numeric($_GET['page'])) {
        throw new Exception_Input('List page is not numeric');
    }
    $page = (int)$_GET['page'] - 1;
}

$perPage = $GLOBALS['phorkie']['cfg']['perPage'];
list($repos, $repoCount, $page) = $rs->getList($page, $perPage);

$pager = new Html_Pager(
    $repoCount, $perPage, $page + 1, 'list/%d'
);

$db = new Database();
render(
    'list',
    array(
        'repos'   => $repos,
        'pager'   => $pager,
        'recents' => $db->getSearch()->listAll(0, 5, 'modate', 'desc'),
        'dh'      => new \Date_HumanDiff(),
    )
);
?>
