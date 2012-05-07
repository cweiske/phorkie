<?php
namespace phorkie;

class Database_Adapter_Null_Search implements Database_ISearch
{
    public function search($term, $page = 0, $perPage = 10)
    {
        $sres = new Search_Result();
        $sres->results = 0;
        $sres->page    = $page;
        $sres->perPage = $perPage;
        return $sres;
    }

    public function listAll($page = 0, $perPage = 10, $sort = 'id', $sortOrder = null)
    {
        $sres = new Search_Result();
        $sres->results = 0;
        $sres->page    = $page;
        $sres->perPage = $perPage;
        return $sres;
    }
}

?>
