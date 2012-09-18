<?php
namespace phorkie;

class ForkRemote
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function parse()
    {
        $scheme = parse_url($this->url, PHP_URL_SCHEME);
        switch ($scheme) {
        case 'git':
            //clearly a git url
            $this->gitUrl = $this->url;
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
    }
}

?>
