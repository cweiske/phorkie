<?php
namespace phorkie;

interface Database_IIndexer
{
    public function addRepo(Repository $repo, $crdate = null, $modate = null);
    public function updateRepo(Repository $repo, $crdate = null, $modate = null);
    public function deleteAllRepos();
    public function deleteRepo(Repository $repo);
}

?>
