<?php
namespace phorkie;

class FlashMessage
{
    public static function save($msg, $type = 'success')
    {
        $_SESSION['flashmessages'][] = array(
            'msg'  => $msg,
            'type' => $type
        );
    }

    public static function getAll()
    {
        if (!isset($_SESSION['flashmessages'])
            || !is_array($_SESSION['flashmessages'])
        ) {
            return array();
        }

        $msgs = $_SESSION['flashmessages'];
        unset($_SESSION['flashmessages']);
        return $msgs;
    }
}
?>
