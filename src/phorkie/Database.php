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
        //FIXME
    }

}

?>
