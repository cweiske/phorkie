<?php
namespace phorkie;
/**
 * Show an access denied error
 */

render(
    'forbidden',
    array(
        'identity' => isset($_SESSION['identity']) ? $_SESSION['identity'] : null
    )
);
exit();
?>
