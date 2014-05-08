<?php
namespace phorkie;
/**
 * Display paste contents
 */
$reqWritePermissions = false;
require_once 'www-header.php';

$repo = new Repository();
$repo->loadFromRequest();

header('X-Pingback: ' . $repo->getLink('linkback', null, true));
header(
    'Link: <' . $repo->getLink('linkback', null, true) . '>;'
    . 'rel="http://webmention.org/"'
);

render(
    'display',
    array(
        'repo' => $repo,
        'dh'   => new \Date_HumanDiff(),
        'htmlhelper' => new HtmlHelper(),
        'flashmessages' => FlashMessage::getAll(),
    )
);
?>
