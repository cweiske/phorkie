<?php
namespace phorkie;

/**
 * Send out linkbacks for the remote paste URL when it gets forked here
 */
class Notificator_Linkback
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Send linkback on "create" events to remote repositories
     */
    public function send($event, Repository $repo)
    {
        if ($this->config === false) {
            return;
        }

        if ($event != 'create') {
            return;
        }

        $origin = $repo->getConnectionInfo()->getOrigin();
        if ($origin === null) {
            return;
        }
        $originWebUrl = $origin->getWebUrl(true);
        if ($originWebUrl === null) {
            return;
        }


        $this->pbc = new \PEAR2\Services\Linkback\Client();
        $req = $this->pbc->getRequest();
        $req->setConfig(
            array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false
            )
        );
        $this->pbc->setRequestTemplate($req);
        $req->setHeader('user-agent', 'phorkie');
        try {
            $res = $this->pbc->send(
                $repo->getLink('display', null, true),
                $originWebUrl
            );
        } catch (\Exception $e) {
            //FIXME: log errors
        }
    }
}
?>
