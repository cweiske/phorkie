<?php
namespace phorkie;

class Indexer_Elasticsearch
{
    public function __construct()
    {
        $this->searchInstance = $GLOBALS['phorkie']['cfg']['elasticsearch'];
    }


    public function addRepo(Repository $repo)
    {
        $this->updateRepo($repo);
    }

    public function updateRepo(Repository $repo)
    {
        //add repository
        $r = new \HTTP_Request2(
            $this->searchInstance . 'repo/' . $repo->id,
            \HTTP_Request2::METHOD_PUT
        );
        $r->setBody(
            json_encode(
                (object)array(
                    'id' => $repo->id,
                    'description' => $repo->getDescription(),
                )
            )
        );
        $r->send();

        //add files
        foreach ($repo->getFiles() as $file) {
            $r = new \HTTP_Request2(
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

    public function deleteRepo(Repository $repo)
    {
        //delete repository from index
        $r = new \HTTP_Request2(
            $this->searchInstance . 'repo/' . $repo->id,
            \HTTP_Request2::METHOD_DELETE
        );
        $r->send();

        //delete files of that repository
        $r = new \HTTP_Request2(
            $this->searchInstance . 'file/_query',
            \HTTP_Request2::METHOD_DELETE
        );
        $r->setBody(
            json_encode(
                (object)array(
                    '_parent' => 'repo#' . $repo->id
                )
            )
        );
        $r->send();
    }

}

?>
