<?php
namespace phorkie;

class Database
{
    public $adapter = null;
    public $prefix = '\phorkie\Database_Adapter_Null';

    public function __construct()
    {
        if ($GLOBALS['phorkie']['cfg']['elasticsearch'] != '') {
            $this->adapter = 'Elasticsearch';
            $this->prefix  = '\phorkie\Database_Adapter_Elasticsearch';
        }
    }
    public function getSearch()
    {
        $class = $this->prefix . '_Search';
        return new $class();
    }

    public function getIndexer()
    {
        $class = $this->prefix . '_Indexer';
        return new $class();
    }

    public function getSetup()
    {
        $class = $this->prefix . '_Setup';
        return new $class();
    }
}

?>
