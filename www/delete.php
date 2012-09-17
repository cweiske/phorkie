<?php
namespace phorkie;
/**
 * Delete paste or ask for deletion
 */
require_once 'www-header.php';
if ($GLOBALS['phorkie']['auth']['secure'] > 0) {
    include_once 'secure.php';
}

$repo = new Repository();
$repo->loadFromRequest();

if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception_Input('Deleting only possible via POST');
    }
    $repo->delete();
    redirect('/');
}

render(
    'delete',
    array('repo' => $repo)
);
?>
