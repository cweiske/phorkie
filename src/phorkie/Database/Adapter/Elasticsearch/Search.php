<?php
namespace phorkie;

class Database_Adapter_Elasticsearch_Search implements Database_ISearch
{
    protected static $sortMap = array(
        'id' => array('id', 'asc'),
        'crdate' => array('crdate', 'desc'),
        'tstamp' => array('tstamp', 'desc'),
    );

    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }

    /**
     * List all repositories
     *
     * @param integer $page    Page of search results, starting with 0
     * @param integer $perPage Number of results per page
     * @param string  $sort    Sort order. Allowed values:
     *                         - id     - repository id
     *                         - crdate - creation date
     *                         - tstamp - modification date
     *
     * @return Search_Result Search result object
     */
    public function listAll($page = 0, $perPage = 10, $sort = 'id', $sortOrder = null)
    {
        list($sortField, $orderField) = $this->getSortField($sort, $sortOrder);
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/_search',
            \HTTP_Request2::METHOD_GET
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'from'  => $page * $perPage,
                    'size'  => $perPage,
                    'sort'  => array(
                        $sortField => $orderField
                    ),
                    'query' => (object)array(
                        'match_all' => (object)array()
                    ),
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
            $r->loadById($hit->_source->id);
            $r->crdate = strtotime($hit->_source->crdate);
            $sres->repos[] = $r;
        }

        return $sres;
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
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
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
                                        'query' => $term,
                                        'default_operator' => 'AND'
                                    ),
                                ),
                                (object)array(
                                    'has_child' => (object)array(
                                        'type'         => 'file',
                                        'query' => (object)array(
                                            'query_string' => (object)array(
                                                'query' => $term,
                                                'default_operator' => 'AND'
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

    protected function getSortField($sort, $sortOrder)
    {
        if (!isset(self::$sortMap[$sort])) {
            throw new Exception('Invalid sort parameter: ' . $sort);
        }
        if ($sortOrder !== 'asc' && $sortOrder !== 'desc') {
            throw new Exception('Invalid sortOrder parameter: ' . $sortOrder);
        }

        $data = self::$sortMap[$sort];
        if ($sortOrder !== null) {
            $data[1] = $sortOrder;
        }
        return $data;
    }
}

?>
