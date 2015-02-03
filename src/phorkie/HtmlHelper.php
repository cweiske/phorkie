<?php
namespace phorkie;

class HtmlHelper
{
    public function getIconUrl($email, $size = 32)
    {
        if ($email == 'anonymous@phorkie'
            || !$GLOBALS['phorkie']['cfg']['avatars']
        ) {
            return 'phorkie/anonymous.png';
        }

        $s = new \Services_Libravatar();
        $s->detectHttps();
        return $s->url(
            $email,
            array(
                'size'    => $size,
                'default' => Tools::fullUrl('phorkie/anonymous.png')
            )
        );
    }

    public function getLanguageOptions(File $file = null)
    {
        $html = '<option value="_auto_">* automatic *</option>';
        $fileExt = null;
        if ($file !== null) {
            $fileExt = $file->getExt();
        }
        foreach ($GLOBALS['phorkie']['languages'] as $ext => $arLang) {
            if (isset($arLang['show']) && !$arLang['show']) {
                continue;
            }
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                $ext,
                $fileExt == $ext ? ' selected="selected"' : '',
                $arLang['title']
            ) . "\n";
        }
        return $html;
    }

    public function getDomain($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    public function fullUrl($path = '')
    {
        return Tools::fullUrl($path);
    }

    public function mayWriteLocally()
    {
        if ($GLOBALS['phorkie']['auth']['securityLevel'] == 0) {
            //everyone may do everything
            return true;
        }

        $logged_in = false;
        if (!isset($_SESSION['identity'])) {
            //not logged in
        } else if ($GLOBALS['phorkie']['auth']['listedUsersOnly']) {
            if (in_array($_SESSION['identity'], $GLOBALS['phorkie']['auth']['users'])) {
                $logged_in = true;
            }
        } else {
            //session identity exists, no special checks required
            $logged_in = true;
        }

        return $logged_in;
    }

    public function getRepositoryEmbedCode(Repository $repo)
    {
        return '<script src="' . $repo->getLink('embed', null, true) . '"'
            . ' id="phork-script-' . $repo->id . '"'
            . ' type="text/javascript"></script>';
    }
}

?>
