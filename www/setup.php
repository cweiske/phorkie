<?php
/**
 * Check if all is setup correctly
 */
namespace phorkie;
$reqWritePermissions = false;
require_once 'www-header.php';

if (!$GLOBALS['phorkie']['cfg']['setupcheck']) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-type: text/plain');
    echo "Setup check is disabled\n";
    exit(1);
}

header('Content-type: text/plain');
echo "All fine\n";
?>
