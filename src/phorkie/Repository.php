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
        $this->id = (int)$_GET['id'];
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

    public function loadById($id)
    {
        if (!is_numeric($id)) {
            throw new Exception_Input('Paste ID not numeric');
        }
        $this->id = (int)$id;
        $this->loadDirs();
        $this->loadHash();
    }

    public function getVc()
    {
        return new \VersionControl_Git($this->gitDir);
    }

    /**
     * Loads the list of files in this repository
     *
     * @return File[] Array of files
     */
    public function getFiles()
    {
        $files = glob($this->workDir . '/*');
        $arFiles = array();
        foreach ($files as $path) {
            $arFiles[] = new File($path, $this);
        }
        return $arFiles;
    }

    public function getFileByName($name, $bHasToExist = true)
    {
        $base = basename($name);
        if ($base != $name) {
            throw new Exception('No directories supported for now');
        }
        if ($name == '') {
            throw new Exception_Input('Empty file name given');
        }
        $path = $this->workDir . '/' . $base;
        if ($bHasToExist && !is_readable($path)) {
            throw new Exception_Input('File does not exist');
        }
        return new File($path, $this);
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
        return Tools::recursiveDelete($this->workDir)
            && Tools::recursiveDelete($this->gitDir);
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
     * Get a link to the repository
     *
     * @param string $type Link type. Supported are:
     *                     - "commit"
     *                     - "edit"
     *                     - "delete"
     *                     - "delete-confirm"
     *                     - "display"
     *                     - "fork"
     * @param string $option
     *
     * @return string
     */
    public function getLink($type, $option = null)
    {
        if ($type == 'edit') {
            return '/' . $this->id . '/edit';
        } else if ($type == 'display') {
            return '/' . $this->id;
        } else if ($type == 'fork') {
            return '/' . $this->id . '/fork';
        } else if ($type == 'delete') {
            return '/' . $this->id . '/delete';
        } else if ($type == 'delete-confirm') {
            return '/' . $this->id . '/delete/confirm';
        } else if ($type == 'commit') {
            return '/' . $this->id . '/' . $option;
        }
        throw new Exception('Unknown link type');
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

            $arLineParts = explode(' ', trim($arOutput[$current + 4]));
            $commit->filesChanged = $arLineParts[0];
            $commit->linesAdded   = $arLineParts[3];
            $commit->linesDeleted = $arLineParts[5];

            $current += 6;

            $arCommits[] = $commit;
        }

        return $arCommits;
    }
}

?>
