<?php
namespace phorkie;

class Search_Result
{
    public $repos;
    public $results;
    public $page;
    public $perPage;

    public function getRepos()
    {
        return $this->repos;
    }

    /**
     * Returns the number of results
     *
     * @return integer Number of results
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Returns the number of the current page, 0 based
     *
     * @return integer Number of current page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the search results per page
     *
     * @return integer Number of results per page
     */
    public function getPerPage()
    {
        return $this->perPage;
    }
}

?>
