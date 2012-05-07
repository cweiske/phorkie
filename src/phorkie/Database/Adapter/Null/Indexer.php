<?php
namespace phorkie;

class Database_Adapter_Null_Indexer
{
    public function addRepo(Repository $repo, $crdate = null) {}

    public function updateRepo(Repository $repo, $crdate = null) {}

    public function deleteAllRepos() {}
    public function deleteRepo(Repository $repo) {}
}

?>
