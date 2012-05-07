<?php
namespace phorkie;

class Database
{
    public function getSearch()
    {
        return new Search_Elasticsearch();
    }

    public function getIndexer()
    {
        return new Indexer_Elasticsearch();
    }

    public function getSetup()
    {
        return new Database_Setup_Elasticsearch();
    }
}

?>
