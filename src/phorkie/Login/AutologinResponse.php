<?php
namespace phorkie;

class Login_AutologinResponse
{
    /**
     * 'error' or 'ok'
     *
     * @var string
     */
    public $status;

    /**
     * Status message
     *
     * @var string
     */
    public $message;

    public $name;
    public $identity;

    public function __construct($status = 'error', $message = null)
    {
        $this->status  = $status;
        $this->message = $message;
    }

    public function send()
    {
        if ($this->status == 'error') {
            //Cookie to prevent trying autologin again and again.
            // After 1 hour the cookie expires and autologin is tried again.
            setcookie('tried-autologin', '1', time() + 60 * 60);
        }

        $data = htmlspecialchars(json_encode($this), ENT_NOQUOTES);
        header('Content-type: text/html');
        echo <<<XML
<html>
 <head>
  <title>Autologin response</title>
  <script type="text/javascript">
    parent.notifyAutologin($data);
  </script>
 </head>
 <body></body>
</html>

XML;
    }
}
?>
