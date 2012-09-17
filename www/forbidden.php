<?php
namespace phorkie;
/**
 * Show an access denied error
 */

header('HTTP/1.0 403 Forbidden');
render(
    'forbidden',
    array(
        'identity' => isset($_SESSION['identity']) ? $_SESSION['identity'] : null
    )
);
exit();
?>
