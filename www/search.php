<?php
namespace phorkie;
/**
 * Search for a search term
 */
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
    $page = (int)$_GET['page'];
}
$perPage = 10;

$db     = new Database();
$search = $db->getSearch();

$sres = $search->search($query, $page, $perPage);
render(
    'search',
    array(
        'query' => $query,
        'sres'  => $sres,
    )
);
?>
