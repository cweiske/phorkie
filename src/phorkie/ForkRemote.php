<?php
namespace phorkie;

class ForkRemote
{
    /**
     * Contains error message when parse() failed
     */
    public $error;

    protected $url;

    /**
     * Array with keys (URL title) and values (arrays of urls)
     * Only supported URLs are included.
     *
     * @var array
     */
    protected $arGitUrls;



    public function __construct($url)
    {
        $this->url = trim($url);
    }

    public function parse()
    {
        if ($this->url == '') {
            $this->error = 'Empty fork URL';
            return false;
        }

        $arUrl  = parse_url($this->url);
        $scheme = isset($arUrl['scheme']) ? $arUrl['scheme'] : '';

        if ($scheme == 'https' && isset($arUrl['host'])
            && $arUrl['host'] == 'gist.github.com'
        ) {
            $this->arGitUrls[][] = 'git://gist.github.com/'
                . ltrim($arUrl['path'], '/') . '.git';
            return true;
        }

        switch ($scheme) {
        case 'git':
            //clearly a git url
            $this->arGitUrls = array(array($this->url));
            return true;

        case 'ssh':
            //FIXME: maybe loosen this when we know how to skip the
            //"do you trust this server" question of ssh
            $this->error = 'ssh:// URLs are not supported';
            return false;

        case 'http':
        case 'https':
            return $this->extractUrlsFromHtml($this->url);
        }

        $this->error = 'Unknown URLs scheme: ' . $scheme;
        return false;
    }

    protected function extractUrlsFromHtml($url)
    {
        //HTML is not necessarily well-formed, and Gitorious has many problems
        // in this regard
        //$sx = simplexml_load_file($url);
        libxml_use_internal_errors(true);
        $sx = simplexml_import_dom(\DomDocument::loadHtmlFile($url));
        $elems = $sx->xpath('//*[@rel="vcs-git"]');
        $titles = $sx->xpath('/html/head/title');
        $pageTitle = $this->cleanPageTitle((string) reset($titles));

        $count = $anonymous = 0;
        foreach ($elems as $elem) {
            if (!isset($elem['href'])) {
                continue;
            }
            $str = (string)$elem;
            if (isset($elem['title'])) {
                //<link href=".." rel="vcs-git" title="title" />
                $title = (string)$elem['title'];
            } else if ($str != '') {
                //<a href=".." rel="vcs-git">title</a>
                $title = $str;
            } else if ($pageTitle != '') {
                $title = $pageTitle;
            } else {
                $title = 'Unnamed repository #' . ++$anonymous;
            }
            $url = (string)$elem['href'];
            if ($this->isSupported($url)) {
                ++$count;
                $this->arGitUrls[$title][] = $url;
            }
        }

        if ($count > 0) {
            return true;
        }

        $this->error = 'No git:// clone URL found';
        return false;
    }

    /**
     * Iterate through all git urls and return one if there is only
     * one supported one.
     *
     * @return mixed Boolean false or array with keys "url" and "title"
     */
    public function getUniqueGitUrl()
    {
        $nFound = 0;
        foreach ($this->arGitUrls as $title => $arUrls) {
            foreach ($arUrls as $url) {
                $nFound++;
                $uniqueUrl = array('url' => $url, 'title' => $title);
            }
        }

        if ($nFound == 1) {
            return $uniqueUrl;
        }
        return false;
    }

    public function getGitUrls()
    {
        return $this->arGitUrls;
    }

    /**
     * Get the URL from which the git URL was derived, often
     * the HTTP URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function isSupported($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return $scheme == 'git'
            || $scheme == 'http' || $scheme == 'https';
    }

    /**
     * Remove application names from HTML page titles
     *
     * @param string $title HTML page title
     *
     * @return string Cleaned HTML page title
     */
    protected function cleanPageTitle($title)
    {
        $title = trim($title);
        if (substr($title, -9) == '- phorkie') {
            $title = trim(substr($title, 0, -9));
        }

        return $title;
    }
}

?>
