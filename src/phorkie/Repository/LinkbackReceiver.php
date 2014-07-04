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

    /**
     * Stores the linkback as remote fork in the paste repository.
     *
     * @param string $target     Target URI that should be linked in $source
     * @param string $source     Linkback source URI that should link to target
     * @param string $sourceBody Content of $source URI
     * @param object $res        HTTP response from fetching $source
     *
     * @return void
     *
     * @throws SPb\Exception When storing the linkback fatally failed
     */
    public function storeLinkback(
        $target, $source, $sourceBody, \HTTP_Request2_Response $res
    ) {
        //FIXME: deleted
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

        $arRemoteCloneUrls = $this->localizeGitUrls($hp->getGitUrls());

        $remoteCloneUrl = $remoteTitle = null;
        if (count($arRemoteCloneUrls)) {
            reset($arRemoteCloneUrls);
            list($remoteCloneUrl, $remoteTitle) = each($arRemoteCloneUrls);
        }
        $remoteid = 'fork-' . uniqid();
        //check if we already know this remote
        foreach ($forks as $remote) {
            if (isset($arRemoteCloneUrls[$remote->getCloneUrl()])) {
                $remoteTitle = $arRemoteCloneUrls[$remote->getCloneUrl()];
                $remoteid = $remote->getName();
                break;
            } else if ($source == $remote->getWebURL(true)) {
                $remoteid = $remote->getName();
                break;
            }
        }

        $vc = $this->repo->getVc();
        if (!$this->isLocalWebUrl($source)) {
            //only add remote homepage; we can calculate local ones ourselves
            $vc->getCommand('config')
                ->addArgument('remote.' . $remoteid . '.homepage')
                ->addArgument($source)
                ->execute();
        }
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

    /**
     * Check if the given full URL is the URL of a local repository
     *
     * @return Repository
     */
    protected function isLocalWebUrl($url)
    {
        $base = Tools::fullUrl();
        if (substr($url, 0, strlen($base)) != $base) {
            //base does not match
            return false;
        }

        $remainder = substr($url, strlen($base));
        if (!is_numeric($remainder)) {
            return false;
        }
        try {
            $repo = new Repository();
            $repo->loadById($remainder);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Convert an array of git urls to local URLs if possible and serialize them
     * into a simple array.
     *
     * @param array $arGitUrls Array of array of urls. Main key is the title of
     *                         the URL array.
     *
     * @return array Key is the git clone URL, value the title of the remote
     */
    protected function localizeGitUrls($arGitUrls)
    {
        $pub = $pri = null;
        if (isset($GLOBALS['phorkie']['cfg']['git']['public'])) {
            $pub = $GLOBALS['phorkie']['cfg']['git']['public'];
        }
        if (isset($GLOBALS['phorkie']['cfg']['git']['private'])) {
            $pri = $GLOBALS['phorkie']['cfg']['git']['private'];
        }

        $arRemoteCloneUrls = array();
        foreach ($arGitUrls as $remoteTitle => $arUrls) {
            foreach ($arUrls as $remoteCloneUrl) {
                if ($pub !== null
                    && substr($remoteCloneUrl, 0, strlen($pub)) == $pub
                    && substr($remoteCloneUrl, -4) == '.git'
                ) {
                    $id = substr($remoteCloneUrl, strlen($pub), -4);
                    $repo = new Repository();
                    try {
                        $repo->loadById($id);
                        $arRemoteCloneUrls[$repo->gitDir] = $remoteTitle;
                    } catch (Exception $e) {
                    }
                } else if ($pri !== null
                    && substr($remoteCloneUrl, 0, strlen($pri)) == $pri
                    && substr($remoteCloneUrl, -4) == '.git'
                ) {
                    $id = substr($remoteCloneUrl, strlen($pri), -4);
                    $repo = new Repository();
                    try {
                        $repo->loadById($id);
                        $arRemoteCloneUrls[$repo->gitDir] = $remoteTitle;
                    } catch (Exception $e) {
                    }
                } else {
                    $arRemoteCloneUrls[$remoteCloneUrl] = $remoteTitle;
                }
            }
        }
        return $arRemoteCloneUrls;
    }
}
?>
