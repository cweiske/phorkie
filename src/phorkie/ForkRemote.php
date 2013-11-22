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
        $hp = new HtmlParser();
        $ret = $hp->extractGitUrls($this->url);
        $this->arGitUrls = $hp->getGitUrls();
        $this->error = $hp->error;

        return $ret;
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
}
?>
