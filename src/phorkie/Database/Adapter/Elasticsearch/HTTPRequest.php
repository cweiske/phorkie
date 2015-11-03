<?php
namespace phorkie;

class Database_Adapter_Elasticsearch_HTTPRequest extends \HTTP_Request2
{
    public $allow404 = false;

    public function send()
    {
        $res = parent::send();
        $mainCode = intval($res->getStatus() / 100);
        if ($mainCode === 2) {
            return $res;
        }

        if ($this->allow404 && $res->getStatus() == 404) {
            return $res;
        }
        $js = json_decode($res->getBody());
        if (isset($js->error)) {
            $error = json_encode($js->error);
        } else {
            $error = $res->getBody();
        }

        throw new Exception(
            'Error in elasticsearch communication at '
            . $this->getMethod() . ' ' . (string) $this->getUrl()
            . ' (status code ' . $res->getStatus() . '): '
            . $error
        );
    }
}

?>
