<?php
namespace phorkie;

class HtmlParser
{
    /**
     * Contains error message when parse() failed
     */
    public $error;

    /**
     * Array with keys (URL title) and values (arrays of urls)
     * Only supported URLs are included.
     *
     * @var array
     */
    protected $arGitUrls;



    /**
     * Extract git URLs from the given URL, eventually fetching
     * HTML and extracting URLs from there.
     *
     * Sets $error and $arGitUrls class variables
     *
     * @param string $url  Git or HTTP URL
     * @param string $html HTML content of $url
     *
     * @return boolean True when all went well, false in case of an error
     * @uses   $error
     * @uses   $arGitUrls
     */
    public function extractGitUrls($url, $html = null)
    {
        if ($url == '') {
            $this->error = 'Empty fork URL';
            return false;
        }

        $arUrl  = parse_url($url);
        $scheme = isset($arUrl['scheme']) ? $arUrl['scheme'] : '';

        if ($scheme == 'https' && isset($arUrl['host'])
            && $arUrl['host'] == 'gist.github.com'
        ) {
            //FIXME: title
            $this->arGitUrls[][] = 'git://gist.github.com/'
                . ltrim($arUrl['path'], '/') . '.git';
            return true;
        }

        switch ($scheme) {
        case 'git':
            //clearly a git url
            $this->arGitUrls = array(array($url));
            return true;

        case 'ssh':
            //FIXME: maybe loosen this when we know how to skip the
            //"do you trust this server" question of ssh
            $this->error = 'ssh:// URLs are not supported';
            return false;

        case 'http':
        case 'https':
            return $this->extractUrlsFromHtml($url, $html);
        }

        $this->error = 'Unknown URLs scheme: ' . $scheme;
        return false;
    }

    protected function extractUrlsFromHtml($url, $html = null)
    {
        //HTML is not necessarily well-formed, and Gitorious has many problems
        // in this regard
        //$sx = simplexml_load_file($url);

        libxml_use_internal_errors(true);
        if ($html === null) {
            $sx = simplexml_import_dom(\DOMDocument::loadHTMLFile($url));
        } else {
            $sx = simplexml_import_dom(\DOMDocument::loadHTML($html));
        }

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

    public function getGitUrls()
    {
        return $this->arGitUrls;
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

    public function isSupported($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return $scheme == 'git'
            || $scheme == 'http' || $scheme == 'https';
    }

}
?>
