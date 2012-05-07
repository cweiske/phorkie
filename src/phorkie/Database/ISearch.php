<?php
namespace phorkie;

interface Database_ISearch
{
    public function search($term, $page = 0, $perPage = 10);
}

?>
