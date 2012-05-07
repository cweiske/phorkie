<?php
namespace phorkie;

class Indexer_Elasticsearch
{
    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }


    public function addRepo(Repository $repo, $crdate = null)
    {
        if ($crdate == null) {
            $crdate = time();
        }
        $this->updateRepo($repo, $crdate);
    }

    public function updateRepo(Repository $repo, $crdate = null)
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
        if ($crdate !== null) {
            $repoData['crdate'] = gmdate('c', $crdate);
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

    public function deleteAllRepos()
    {
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'repo/_query',
            \HTTP_Request2::METHOD_DELETE
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'match_all' => (object)array()
                )
            )
        );
        $r->send();
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'file/_query',
            \HTTP_Request2::METHOD_DELETE
        );
        $r->setBody(
            json_encode(
                (object)array(
            'match_all' => (object)array()
                )
            )
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
        $r->send();

        $this->deleteRepoFiles($repo);
    }

    protected function deleteRepoFiles(Repository $repo)
    {
        //delete files of that repository
        $r = new Database_Adapter_Elasticsearch_HTTPRequest(
            $this->searchInstance . 'file/_query',
            \HTTP_Request2::METHOD_DELETE
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'field' => (object)array(
                        '_parent' => $repo->id
                    )
                )
            )
        );
        $r->send();
    }

}

?>
