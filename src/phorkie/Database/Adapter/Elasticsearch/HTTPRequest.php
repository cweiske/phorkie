<?php
namespace phorkie;

class Database_Adapter_Elasticsearch_HTTPRequest extends \HTTP_Request2
{
    public function send()
    {
        $res = parent::send();
        $mainCode = intval($res->getStatus() / 100);
        if ($mainCode != 2) {
            $js = json_decode($res->getBody());
            if (isset($js->error)) {
                $error = $js->error;
            } else {
                $error = $res->getBody();
            }
            throw new Exception(
                'Error in elasticsearch communication: ' . $error
            );
        }
        return $res;
    }
}

?>
