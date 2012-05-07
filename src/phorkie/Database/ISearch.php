<?php
namespace phorkie;

interface Database_ISearch
{
    public function search($term, $page = 0, $perPage = 10);

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
    public function listAll($page = 0, $perPage = 10, $sort = 'id', $sortOrder = null);

}

?>
