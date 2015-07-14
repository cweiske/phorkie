<?php
namespace phorkie;


class Repository
{
    /**
     * Repository ID (number in repositories directory)
     *
     * @var integer
     */
    public $id;

    /**
     * Full path to the .git repository
     *
     * @var string
     */
    public $gitDir;

    /**
     * Full path to the work tree directory
     *
     * @var string
     */
    public $workDir;

    /**
     * Revision of the repository that shall be shown
     *
     * @var string
     */
    public $hash;

    /**
     * Commit message of the last (or current) revision
     *
     * @var string
     */
    public $message;


    /**
     * Load Repository data from GET-Request
     *
     * @return void
     *
     * @throws Exception When something is wrong
     */
    public function loadFromRequest()
    {
        if (!isset($_GET['id'])) {
            throw new Exception_Input('Paste ID missing');
        }
        if (!is_numeric($_GET['id'])) {
            throw new Exception_Input('Paste ID not numeric');
        }
        if (isset($_GET['rev'])) {
            $this->hash = $_GET['rev'];
        }

        $this->id = (int)$_GET['id'];
        $this->loadDirs();
        $this->loadHash();
        $this->loadMessage();
    }

    public function loadById($id)
    {
        if (!is_numeric($id)) {
            throw new Exception_Input('Paste ID not numeric');
        }
        $this->id = (int)$id;
        $this->loadDirs();
        $this->loadHash();
    }

    protected function loadDirs()
    {
        $gitDir = $GLOBALS['phorkie']['cfg']['gitdir'] . '/' . $this->id . '.git';
        if (!is_dir($gitDir)) {
            throw new Exception_NotFound(
                sprintf('Paste %d .git dir not found', $this->id)
            );
        }
        $this->gitDir = $gitDir;

        $workDir = $GLOBALS['phorkie']['cfg']['workdir'] . '/' . $this->id;
        if (!is_dir($workDir)) {
            throw new Exception_NotFound(
                sprintf('Paste %d work dir not found', $this->id)
            );
        }
        $this->workDir = $workDir;
    }

    public function loadHash()
    {
        if ($this->hash !== null) {
            return;
        }

        $output = $this->getVc()->getCommand('log')
            ->setOption('pretty', 'format:%H')
            ->setOption('max-count', 1)
            ->execute();
        $output = trim($output);
        if (strlen($output) !== 40) {
            throw new Exception(
                'Loading commit hash failed: ' . $output
            );
        }
        $this->hash = $output;
    }

    public function reloadHash()
    {
        $this->hash = null;
        return $this->loadHash();
    }

    /**
     * Populates $this->message
     *
     * @return void
     */
    public function loadMessage()
    {
        $rev = (isset($this->hash)) ? $this->hash : 'HEAD';
        $output = $this->getVc()->getCommand('log')
            ->setOption('oneline')
            ->addArgument('-1')
            ->addArgument($rev)
            ->execute();
        $output = trim($output);
        if (strpos($output, ' ') > 0) {
            $output = substr($output, strpos($output, ' '), strlen($output));
            $this->message = trim($output);
        } else {
            $this->message = "This commit message intentionally left blank.";
        }
    }

    public function getVc()
    {
        return new \VersionControl_Git($this->gitDir);
    }

    /**
     * Loads the list of files in this repository
     *
     * @return File[] Array of file objects
     */
    public function getFiles()
    {
        $files = $this->getFilePaths();
        $arFiles = array();
        foreach ($files as $name) {
            $arFiles[] = new File($name, $this);
        }
        return $arFiles;
    }

    /**
     * Decodes unicode characters in git filenames
     * They begin and end with double quote characters, and may contain
     * backslash + 3 letter octal code numbers representing the character.
     *
     * For example,
     * > "t\303\244st.txt"
     * means
     * > täst.txt
     *
     * On the shell, you can pipe them into "printf" and have them decoded.
     *
     * @param string Encoded git file name
     *
     * @return string Decoded file name
     */
    protected function decodeFileName($name)
    {
        $name = substr($name, 1, -1);
        $name = str_replace('\"', '"', $name);
        $name = preg_replace_callback(
            '#\\\\[0-7]{3}#',
            function ($ar) {
                return chr(octdec(substr($ar[0], 1)));
            },
            $name
        );
        return $name;
    }

    /**
     * Return array with all file paths in this repository
     *
     * @return array
     */
    protected function getFilePaths()
    {
        if ($this->hash === null) {
            $hash = 'HEAD';
        } else {
            $hash = $this->hash;
        }
        $output = $this->getVc()->getCommand('ls-tree')
            ->setOption('r')
            ->setOption('name-only')
            ->addArgument($hash)
            ->execute();
        $files = explode("\n", trim($output));
        foreach ($files as &$file) {
            if ($file{0} == '"') {
                $file = $this->decodeFileName($file);
            }
        }
        return $files;
    }

    public function getFileByName($name, $bHasToExist = true)
    {
        $name = Tools::sanitizeFilename($name);
        if ($name == '') {
            throw new Exception_Input('Empty file name given');
        }

        if ($bHasToExist) {
            $files = $this->getFilePaths();
            if (array_search($name, $files) === false) {
                throw new Exception_Input('File does not exist');
            }
        }
        return new File($name, $this);
    }

    public function hasFile($name)
    {
        try {
            $this->getFileByName($name);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Permanently deletes the paste repository without any way to get
     * it back.
     *
     * @return boolean True if all went well, false if not
     */
    public function delete()
    {
        $db = new Database();
        $db->getIndexer()->deleteRepo($this);

        $bOk = Tools::recursiveDelete($this->workDir)
            && Tools::recursiveDelete($this->gitDir);

        $not = new Notificator();
        $not->delete($this);

        return $bOk;
    }

    public function getTitle()
    {
        $desc = $this->getDescription();
        if (trim($desc) != '') {
            return $desc;
        }

        return 'paste #' . $this->id;
    }

    public function getDescription()
    {
        if (!is_readable($this->gitDir . '/description')) {
            return null;
        }
        return file_get_contents($this->gitDir . '/description');
    }

    public function setDescription($description)
    {
        file_put_contents($this->gitDir . '/description', $description);
    }

    /**
     * @return array Array with keys "email" and "name"
     */
    public function getOwner()
    {
        try {
            $name = $this->getVc()->getCommand('config')
                ->addArgument('owner.name')->execute();
        } catch (\VersionControl_Git_Exception $e) {
            $name = $GLOBALS['phorkie']['auth']['anonymousName'];
        }
        try {
            $email = $this->getVc()->getCommand('config')
                ->addArgument('owner.email')->execute();
        } catch (\VersionControl_Git_Exception $e) {
            $email = $GLOBALS['phorkie']['auth']['anonymousEmail'];
        }

        return array('name' => trim($name), 'email' => trim($email));
    }

    /**
     * Get a link to the repository
     *
     * @param string  $type   Link type. Supported are:
     *                        - "edit"
     *                        - "delete"
     *                        - "delete-confirm"
     *                        - "display"
     *                        - "embed"
     *                        - "fork"
     *                        - "revision"
     * @param string  $option Additional link option, e.g. revision number
     * @param boolean $full   Return full URL or normal relative
     *
     * @return string
     */
    public function getLink($type, $option = null, $full = false)
    {
        if ($type == 'edit') {
            $link = $this->id . '/edit';
            if ($option !== null) {
                $link .= '/' . urlencode($option);
            }
        } else if ($type == 'display') {
            $link = $this->id;
        } else if ($type == 'fork') {
            $link = $this->id . '/fork';
        } else if ($type == 'doap') {
            $link = $this->id . '/doap';
        } else if ($type == 'delete') {
            $link = $this->id . '/delete';
        } else if ($type == 'delete-confirm') {
            $link = $this->id . '/delete/confirm';
        } else if ($type == 'embed') {
            $link = $this->id . '/embed';
        } else if ($type == 'oembed-json') {
            $link = 'oembed.php?format=json&url='
                . urlencode($this->getLink('display', null, true));
        } else if ($type == 'oembed-xml') {
            $link = 'oembed.php?format=xml&url='
                . urlencode($this->getLink('display', null, true));
        } else if ($type == 'remotefork') {
            return 'web+fork:' . $this->getLink('display', null, true);
        } else if ($type == 'revision') {
            $link = $this->id . '/rev/' . $option;
        } else if ($type == 'linkback') {
            $link = $this->id . '/linkback';
        } else {
            throw new Exception('Unknown link type');
        }

        if ($full) {
            $link = Tools::fullUrl($link);
        }
        return $link;
    }

    public function getCloneURL($public = true)
    {
        $var = $public ? 'public' : 'private';
        if (isset($GLOBALS['phorkie']['cfg']['git'][$var])) {
            return $GLOBALS['phorkie']['cfg']['git'][$var] . $this->id . '.git';
        }
        return null;
    }

    /**
     * Returns the history of the repository.
     * We don't use VersionControl_Git's rev list fetcher since it does not
     * give us separate email addresses and names, and it does not give us
     * the amount of changed (added/deleted) lines.
     *
     * @return array Array of history objects
     */
    public function getHistory()
    {
        $output = $this->getVc()->getCommand('log')
            ->setOption('pretty', 'format:commit %H%n%at%n%an%n%ae')
            ->setOption('max-count', 10)
            ->setOption('shortstat')
            ->execute();

        $arCommits = array();
        $arOutput = explode("\n", $output);
        $lines = count($arOutput);
        $current = 0;
        while ($current < $lines) {
            $commit = new Repository_Commit();
            list($name,$commit->hash) = explode(' ', $arOutput[$current]);
            if ($name !== 'commit') {
                throw new Exception(
                    'Git log output format not as expected: ' . $arOutput[$current]
                );
            }
            $commit->committerTime  = $arOutput[$current + 1];
            $commit->committerName  = $arOutput[$current + 2];
            $commit->committerEmail = $arOutput[$current + 3];

            if (substr($arOutput[$current + 4], 0, 1) != ' ') {
                //commit without changed lines
                $arCommits[] = $commit;
                $current += 4;
                continue;
            }

            $arLineParts = explode(' ', trim($arOutput[$current + 4]));
            $commit->filesChanged = $arLineParts[0];
            $commit->linesAdded   = $arLineParts[3];
            if (isset($arLineParts[5])) {
                $commit->linesDeleted = $arLineParts[5];
            }

            $current += 6;

            $arCommits[] = $commit;
        }

        return $arCommits;
    }

    /**
     * @return Repository_ConnectionInfo
     */
    public function getConnectionInfo()
    {
        return new Repository_ConnectionInfo($this);
    }
}

?>
