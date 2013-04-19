<?php
/**
 * Generate an atom feed with the 10 most recently created pastes
 */
namespace phorkie;
$reqWritePermissions = false;
require_once 'www-header.php';

$db = new Database();
header('Content-Type: application/atom+xml');
render(
    'feed-new',
    array(
        'pastes'  => $db->getSearch()->listAll(0, 10, 'crdate', 'desc'),
        'url'     => Tools::fullUrl(),
        'feedurl' => Tools::fullUrl('feed/new'),
    )
);
?>
