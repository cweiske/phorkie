<?php
namespace phorkie;

class Search_Elasticsearch
{
    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }

    /**
     * Search for a given term and return repositories that contain it
     * in their description, file names or file content
     *
     * @param string  $term    Search term
     * @param integer $page    Page of search results, starting with 0
     * @param integer $perPage Number of results per page
     *
     * @return Search_Result Search result object
     */
    public function search($term, $page = 0, $perPage = 10)
    {
        $r = new \HTTP_Request2(
            $this->searchInstance . 'repo/_search',
            \HTTP_Request2::METHOD_GET
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'from' => $page * $perPage,
                    'size' => $perPage,
                    'query' => (object)array(
                        'bool' => (object)array(
                            'should' => array(
                                (object)array(
                                    'query_string' => (object)array(
                                        'query' => $term
                                    ),
                                ),
                                (object)array(
                                    'has_child' => (object)array(
                                        'type'         => 'file',
                                        'query' => (object)array(
                                            'query_string' => (object)array(
                                                'query' => $term
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        $httpRes = $r->send();
        $jRes = json_decode($httpRes->getBody());
        if (isset($jRes->error)) {
            throw new Exception(
                'Search exception: ' . $jRes->error, $jRes->status
            );
        }

        $sres = new Search_Result();
        $sres->results = $jRes->hits->total;
        $sres->page    = $page;
        $sres->perPage = $perPage;

        foreach ($jRes->hits->hits as $hit) {
            $r = new Repository();
            //FIXME: error handling. what about deleted repos?
            $r->loadById($hit->_source->id);
            $sres->repos[] = $r;
        }

        return $sres;
    }
}

?>
