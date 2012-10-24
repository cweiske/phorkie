<?php
namespace phorkie;

class HtmlHelper
{
    public function getIconUrl($email, $size = 32)
    {
        if ($email == 'anonymous@phorkie') {
            return '/phorkie/anonymous.png';
        }

        $s = new \Services_Libravatar();
        return $s->url(
            $email,
            array(
                'size'    => $size,
                'default' => Tools::fullUrl('/phorkie/anonymous.png')
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
}

?>
