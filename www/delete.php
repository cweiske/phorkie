<?php
namespace phorkie;
/**
 * Delete paste or ask for deletion
 */
$reqWritePermissions = true;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception_Input('Deleting only possible via POST');
    }
    $repo->delete();
    redirect(Tools::fullUrl());
}

render(
    'delete',
    array('repo' => $repo)
);
?>
