<?php
namespace phorkie;

class Repository_LinkbackReceiver
    implements \PEAR2\Services\Linkback\Server\Callback\IStorage
{
    protected $repo;

    public function __construct($repo)
    {
        $this->repo = $repo;
    }

    public function storeLinkback(
        $target, $source, $sourceBody, \HTTP_Request2_Response $res
    ) {
        //FIXME: deleted
        //FIXME: updated
        //FIXME: cleanuptask

        $hp = new HtmlParser();
        $ok = $hp->extractGitUrls($source, $sourceBody);
        if ($ok === false) {
            //failed to extract git URL from linkback source
            //FIXME: send exception
            //$hp->error
            return;
        }

        $ci = $this->repo->getConnectionInfo();
        $forks = $ci->getForks();

        $remoteCloneUrl = $remoteTitle = null;
        $arRemoteCloneUrls = array();
        $arGitUrls = $hp->getGitUrls();
        foreach ($arGitUrls as $remoteTitle => $arUrls) {
            foreach ($arUrls as $remoteCloneUrl) {
                $arRemoteCloneUrls[$remoteCloneUrl] = $remoteTitle;
            }
        }

        $remoteid = 'fork-' . uniqid();
        //check if we already know this remote
        foreach ($forks as $remote) {
            if (isset($arRemoteCloneUrls[$remote->getCloneUrl()])
                || $source == $remote->getWebURL(true)
            ) {
                $remoteid = $remote->getName();
                break;
            }
        }

        if ($this->isLocalWebUrl($source)) {
            //convert both web and clone url to local urls
        }

        $vc = $this->repo->getVc();
        $vc->getCommand('config')
            ->addArgument('remote.' . $remoteid . '.homepage')
            ->addArgument($source)
            ->execute();
        if ($remoteTitle !== null) {
            $vc->getCommand('config')
                ->addArgument('remote.' . $remoteid . '.title')
                ->addArgument($remoteTitle)
                ->execute();
        }
        if ($remoteCloneUrl !== null) {
            $vc->getCommand('config')
                ->addArgument('remote.' . $remoteid . '.url')
                ->addArgument($remoteCloneUrl)
                ->execute();
        }
    }

    protected function isLocalWebUrl($url)
    {
        $base = Tools::fullUrl();
        if (substr($url, 0, strlen($base)) != $base) {
            //base does not match
            return false;
        }

        $remainder = substr($url, strlen($base));
        //FIXME: check if it exists
    }
}
?>
