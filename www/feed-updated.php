<?php
/**
 * Generate an atom feed with the 10 most recently updated pastes
 */
namespace phorkie;
$reqWritePermissions = false;
require_once 'www-header.php';

$db = new Database();
header('Content-Type: application/atom+xml');
render(
    'feed-updated',
    array(
        'pastes'  => $db->getSearch()->listAll(0, 10, 'modate', 'desc'),
        'url'     => Tools::fullUrl(),
        'feedurl' => Tools::fullUrl('feed/updated'),
    )
);
?>
