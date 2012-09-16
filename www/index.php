<?php
/**
 * Jump to the index as per the configuration
 */
namespace phorkie;
require_once 'www-header.php';
require_once $GLOBALS['phorkie']['cfg']['index'].".php";
?>
