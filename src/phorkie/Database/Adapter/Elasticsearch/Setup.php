<?php
namespace phorkie;

class Database_Adapter_Elasticsearch_Setup implements Database_ISetup
{
    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }

    public function setup()
    {
        $r = new \HTTP_Request2(
            $this->searchInstance . '/_mapping', \HTTP_Request2::METHOD_GET
        );
        $res = $r->send();
        if ($res->getStatus() == 404) {
            $this->reset();
        }
    }

    public function reset()
    {
        $r = new \HTTP_Request2(
            $this->searchInstance,
            \HTTP_Request2::METHOD_DELETE
        );
        $r->send();

        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance,
            \HTTP_Request2::METHOD_PUT
        );
        $r->send();

        //create mapping
        //mapping for repositories
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/_mapping',
            \HTTP_Request2::METHOD_PUT
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'repo' => (object)array(
                        '_timestamp' => (object)array(
                            'enabled' => true,
                            'path'    => 'tstamp',
                        ),
                        'properties' => (object)array(
                            'id' => (object)array(
                                'type' => 'long'
                            ),
                            'description' => (object)array(
                                'type'  => 'string',
                                'boost' => 2.0
                            ),
                            'crdate' => (object)array(
                                //creation date
                                'type' => 'date',
                            ),
                            'modate' => (object)array(
                                //modification date
                                'type' => 'date',
                            ),
                            'tstamp' => (object)array(
                                //last indexed date
                                'type' => 'date',
                            )
                        )
                    )
                )
            )
        );
        $r->send();

        //mapping for files
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'file/_mapping',
            \HTTP_Request2::METHOD_PUT
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'file' => (object)array(
                        '_parent' => (object)array(
                            'type' => 'repo'
                        ),
                        'properties' => (object)array(
                            'name' => (object)array(
                                'type'  => 'string',
                                'boost' => 1.5
                            ),
                            'extension' => (object)array(
                                'type'  => 'string',
                                'boost' => 1.0
                            ),
                            'content' => (object)array(
                                'type'  => 'string',
                                'boost' => 0.8
                            )
                        )
                    )
                )
            )
        );
        $r->send();
    }

}

?>
