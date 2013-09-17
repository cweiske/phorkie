<?php
namespace phorkie;

/**
 * Send out webhook callbacks when something happens
 */
class Notificator
{
    /**
     * A repository has been created
     */
    public function create(Repository $repo)
    {
        $this->send('create', $repo);
    }

    /**
     * A repository has been modified
     */
    public function edit(Repository $repo)
    {
        $this->send('edit', $repo);
    }

    /**
     * A repository has been deleted
     */
    public function delete(Repository $repo)
    {
        $this->send('delete', $repo);
    }

    /**
     * Call webhook URLs with our payload
     */
    protected function send($event, Repository $repo)
    {
        if (count($GLOBALS['phorkie']['cfg']['webhooks']) == 0) {
            return;
        }
        
        /* slightly inspired by
           https://help.github.com/articles/post-receive-hooks */
        $payload = (object) array(
            'event'  => $event,
            'author' => array(
                'name'  => $_SESSION['name'],
                'email' => $_SESSION['email']
            ),
            'repository' => array(
                'name'        => $repo->getTitle(),
                'url'         => $repo->getLink('display', null, true),
                'description' => $repo->getDescription(),
                'owner'       => $repo->getOwner()
            )
        );
        foreach ($GLOBALS['phorkie']['cfg']['webhooks'] as $url) {
            $req = new \HTTP_Request2($url);
            $req->setMethod(\HTTP_Request2::METHOD_POST)
                ->setHeader('Content-Type: application/vnd.phorkie.webhook+json')
                ->setBody(json_encode($payload));
            try {
                $response = $req->send();
                //FIXME log response codes != 200
            } catch (HTTP_Request2_Exception $e) {
                //FIXME log exceptions
            }
        }
    }
}
?>
