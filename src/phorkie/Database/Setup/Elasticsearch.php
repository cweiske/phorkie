<?php
namespace phorkie;

class Database_Setup_Elasticsearch
{
    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }

    public function setup()
    {
        //create mapping
        //mapping for repositories
        $r = new \HTTP_Request2(
            $this->searchInstance . 'repo/_mapping',
            \HTTP_Request2::METHOD_PUT
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'repo' => (object)array(
                        'properties' => (object)array(
                            'id' => (object)array(
                                'type' => 'long'
                            ),
                            'description' => (object)array(
                                'type'  => 'string',
                                'boost' => 2.0
                            )
                        )
                    )
                )
            )
        );
        $r->send();

        //mapping for files
        $r = new \HTTP_Request2(
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
