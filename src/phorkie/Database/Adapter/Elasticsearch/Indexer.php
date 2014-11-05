<?php
namespace phorkie;

class Database_Adapter_Elasticsearch_Indexer implements Database_IIndexer
{
    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }


    public function addRepo(Repository $repo, $crdate = null, $modate = null)
    {
        if ($crdate == null) {
            $crdate = time();
        }
        if ($modate == null) {
            $modate = time();
        }
        $this->updateRepo($repo, $crdate, $modate);
    }

    public function updateRepo(Repository $repo, $crdate = null, $modate = null)
    {
        //add repository
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/' . $repo->id,
            \HTTP_Request2::METHOD_PUT
        );
        $repoData = array(
            'id'          => $repo->id,
            'description' => $repo->getDescription(),
            'tstamp'      => gmdate('c', time()),
        );
        if ($crdate == null) {
            $crdate = $this->getCrDate($repo);
        }
        if ($crdate !== null) {
            $repoData['crdate'] = gmdate('c', $crdate);
        }
        if ($modate == null) {
            $modate = $this->getMoDate($repo);
        }
        if ($modate !== null) {
            $repoData['modate'] = gmdate('c', $modate);
        }

        $r->setBody(json_encode((object)$repoData));
        $r->send();

        //add files
        //clean up before adding files; files might have been deleted
        $this->deleteRepoFiles($repo);

        foreach ($repo->getFiles() as $file) {
            $r = new Database_Adapter_Elasticsearch_HTTPRequest(
                $this->searchInstance . 'file/?parent=' . $repo->id,
                \HTTP_Request2::METHOD_POST
            );
            $r->setBody(
                json_encode(
                    (object)array(
                        'name'      => $file->getFilename(),
                        'extension' => $file->getExt(),
                        'content'   => $file->isText() ? $file->getContent() : '',
                    )
                )
            );
            $r->send();
        }
    }

    /**
     * When updating the repository, we don't have a creation date.
     * We need to keep it, but elasticsearch does not have a simple way
     * to update some fields only (without using a custom script).
     *
     * @return integer Unix timestamp
     */
    protected function getCrDate(Repository $repo)
    {
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/' . $repo->id,
            \HTTP_Request2::METHOD_GET
        );
        $json = json_decode($r->send()->getBody());

        if (!isset($json->_source->crdate)) {
            return null;
        }

        return strtotime($json->_source->crdate);
    }

    /**
     * When updating the repository, we don't have a modification date.
     * We need to keep it, but elasticsearch does not have a simple way
     * to update some fields only (without using a custom script).
     *
     * @return integer Unix timestamp
     */
    protected function getMoDate(Repository $repo)
    {
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/' . $repo->id,
            \HTTP_Request2::METHOD_GET
        );
        $json = json_decode($r->send()->getBody());

        if (!isset($json->_source->modate)) {
            return null;
        }

        return strtotime($json->_source->modate);
    }

    public function deleteAllRepos()
    {
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo',
            \HTTP_Request2::METHOD_DELETE
        );
        $r->send();

        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'file',
            \HTTP_Request2::METHOD_DELETE
        );
        $r->send();
    }

    public function deleteRepo(Repository $repo)
    {
        //delete repository from index
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/' . $repo->id,
            \HTTP_Request2::METHOD_DELETE
        );
        $r->allow404 = true;
        $r->send();

        $this->deleteRepoFiles($repo);
    }

    protected function deleteRepoFiles(Repository $repo)
    {
        //delete files of that repository
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'file/_query'
            . '?q=_parent:' . $repo->id,
            \HTTP_Request2::METHOD_DELETE
        );
        $r->send();
    }

}

?>
