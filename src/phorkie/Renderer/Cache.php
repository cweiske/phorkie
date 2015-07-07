<?php
namespace phorkie;

class Renderer_Cache
{
    /**
     * Converts the code to HTML by fetching it from cache,
     * or by letting the other renderes generate it and then
     * storing it in the cache.
     *
     * @param File        $file File to render
     * @param Tool_Result $res  Tool result to integrate
     *
     * @return string HTML
     */
    public function toHtml(File $file, Tool_Result $res = null)
    {
        $html = null;
        $cacheFile = $this->getCacheFile($file);
        if ($res === null && $cacheFile !== null) {
            $html = $this->loadHtmlFromCache($cacheFile);
        }
        if ($html === null) {
            $html = $this->renderFile($file, $res);
            if ($res === null && $cacheFile !== null) {
                $this->storeHtmlIntoCache($cacheFile, $html);
            }
        }
        return $html;
    }

    protected function renderFile(File $file, Tool_Result $res = null)
    {
        $ext   = $file->getExt();
        $class = '\\phorkie\\Renderer_Unknown';

        if (isset($GLOBALS['phorkie']['languages'][$ext]['renderer'])) {
            $class = $GLOBALS['phorkie']['languages'][$ext]['renderer'];
        } else if ($file->isText()) {
            $class = '\\phorkie\\Renderer_Geshi';
        } else if (isset($GLOBALS['phorkie']['languages'][$ext]['mime'])) {
            $type = $GLOBALS['phorkie']['languages'][$ext]['mime'];
            if (substr($type, 0, 6) == 'image/') {
                $class = '\\phorkie\\Renderer_Image';
            }
        }

        $rend = new $class();
        return $rend->toHtml($file, $res);
    }

    /**
     * @return null|string NULL when there is no cache, string with HTML
     *                     otherwise
     */
    protected function loadHtmlFromCache($cacheFile)
    {
        if (!file_exists($cacheFile)) {
            return null;
        }
        return file_get_contents($cacheFile);
    }

    protected function storeHtmlIntoCache($cacheFile, $html)
    {
        file_put_contents($cacheFile, $html);
    }

    protected function getCacheFile(File $file)
    {
        if (!$GLOBALS['phorkie']['cfg']['cachedir']
            || !is_dir($GLOBALS['phorkie']['cfg']['cachedir'])
        ) {
            return null;
        }

        return $GLOBALS['phorkie']['cfg']['cachedir']
            . '/' . $file->repo->id
            . '-' . $file->repo->hash
            . '-' . str_replace('/', '-', $file->getFilename())
            . '.html';
    }
}
?>
