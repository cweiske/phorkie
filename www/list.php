<?php
/**
 * Fork a repository
 */
namespace phorkie;
require_once 'www-header.php';
$rs = new Repositories();

$page = 0;
if (isset($_GET['page'])) {
    if (!is_numeric($_GET['page'])) {
        throw new Exception_Input('List page is not numeric');
    }
    $page = (int)$_GET['page'];
}

$perPage = 10;
$repos = $rs->getList($page, $perPage);

$links = array('prev' => null, 'next' => null);
if ($page > 0) {
    $links['prev'] = '/list/' . ($page - 1);
    if ($page - 1 == 0) {
        $links['prev'] = '/list';
    }
}
if (count($repos) && count($repos) == $perPage) {
    $links['next'] = '/list/' . ($page + 1);
}

render(
    'list',
    array(
        'repos' => $repos,
        'links' => $links,
    )
);
?>
