<?php
/**
 * Jump to the index as per the configuration
 */
namespace phorkie;
$securityLevel = false;
require_once 'www-header.php';

header(
    'Location: '
    . Tools::fullUrl('/' . $GLOBALS['phorkie']['cfg']['index'])
);
?>
