<?php
namespace phorkie;
/**
 * Search for a search term
 */
$secureAtLevel = '0';
require_once 'www-header.php';

if (!isset($_GET['q']) || $_GET['q'] == '') {
    header('Location: ' . Tools::fullUrl('/list'));
    exit();
}
$query = $_GET['q'];

$page = 0;
if (isset($_GET['page'])) {
    if (!is_numeric($_GET['page'])) {
        throw new Exception_Input('List page is not numeric');
    }
    //PEAR Pager begins at 1
    $page = (int)$_GET['page'] - 1;
}
$perPage = 10;

$db     = new Database();
$search = $db->getSearch();

$sres = $search->search($query, $page, $perPage);

$pager = new Html_Pager(
    $sres->getResults(), $perPage, $page + 1, $sres->getLink($query)
);
render(
    'search',
    array(
        'query' => $query,
        'sres'  => $sres,
        'pager' => $pager
    )
);
?>
