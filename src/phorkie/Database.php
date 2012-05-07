<?php
namespace phorkie;

class Database
{
    public function getSearch()
    {
        return new Database_Adapter_Elasticsearch_Search();
    }

    public function getIndexer()
    {
        return new Database_Adapter_Elasticsearch_Indexer();
    }

    public function getSetup()
    {
        return new Database_Adapter_Elasticsearch_Setup();
    }
}

?>
