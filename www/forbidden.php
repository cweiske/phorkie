<?php
/**
 * Access Denied page
 */
namespace phorkie;
require_once 'www-header.php';

$db = new Database();
render(
    'forbidden',
    array(
        'recents'     => $db->getSearch()->listAll(0, 5, 'crdate', 'desc'),
    )
);
?>
