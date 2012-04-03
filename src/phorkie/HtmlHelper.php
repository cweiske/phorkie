<?php
namespace phorkie;

class HtmlHelper
{
    public function getLanguageOptions(File $file = null)
    {
        $html = '';
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