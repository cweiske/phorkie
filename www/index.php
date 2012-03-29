<?php
namespace Phorkie;
/**
 * Show paste creation form
 *
 * Elements:
 * - description
 * - file name (default: default.php)
 * - content
 *
 * Creates and redirects to display page
 */
require_once 'www-header.php';

$repopo = new Repository_Post();
if ($repopo->process($_POST)) {
    redirect($repopo->repo->getLink('display'));
}

$phork = array(
    '1' => new File(null, null)
);
render('index', array('files' => $phork, 'description' => ''));
?>