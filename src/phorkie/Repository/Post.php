<?php
namespace phorkie;

class Repository_Post
{
    public $repo;

    public function __construct(Repository $repo = null)
    {
        $this->repo = $repo;
    }

    /**
     * Processes the POST data, changes description and files
     *
     * @return boolean True if the post was successful
     */
    public function process($postData, $sessionData)
    {
        if (!isset($postData['files'])) {
            return false;
        }
        if (!$this->hasContent($postData)) {
            return false;
        }

        if (!$this->repo) {
            $this->repo = $this->createRepo();
        }

        $vc = $this->repo->getVc();


        $bChanged = false;
        $bCommit  = false;
        if ($postData['description'] != $this->repo->getDescription()) {
            $this->repo->setDescription($postData['description']);
            $bChanged = true;
        }

        foreach ($postData['files'] as $num => $arFile) {
            $bUpload = false;
            if ($_FILES['files']['error'][$num]['upload'] == 0) {
                //valid file upload
                $bUpload = true;
            } else if ($arFile['content'] == '' && $arFile['name'] == '') {
                //empty (new) file
                continue;
            }

            $orignalName = Tools::sanitizeFilename($arFile['original_name']);
            $name        = Tools::sanitizeFilename($arFile['name']);

            if ($arFile['type'] == '_auto_') {
                //FIXME: upload
                $arFile['type'] = $this->getType($arFile['content']);
            }

            if ($name == '') {
                if ($bUpload) {
                    $name = Tools::sanitizeFilename($_FILES['files']['name'][$num]['upload']);
                } else {
                    $name = $this->getNextNumberedFile('phork')
                        . '.' . $arFile['type'];
                }
            }

            $bNew = false;
            $bDelete = false;
            if (!isset($orignalName) || $orignalName == '') {
                //new file
                $bNew = true;
                if (strpos($name, '.') === false) {
                    //automatically append file extension if none is there
                    $name .= '.' . $arFile['type'];
                }
            } else if (!$this->repo->hasFile($orignalName)) {
                //unknown file
                //FIXME: Show error message
                continue;
            } else if (isset($arFile['delete']) && $arFile['delete'] == 1) {
                $bDelete = true;
            } else if ($orignalName != $name) {
                if (strpos($name, '/') === false) {
                    //ignore names with a slash in it, would be new directory
                    //FIXME: what to do with overwrites?
                    $vc->getCommand('mv')
                        ->addArgument($orignalName)
                        ->addArgument($name)
                        ->execute();
                    $bCommit = true;
                } else {
                    $name = $orignalName;
                }
            }

            $file = $this->repo->getFileByName($name, false);
            if ($bDelete) {
                $command = $vc->getCommand('rm')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bCommit = true;
            } else if ($bUpload) {
                move_uploaded_file(
                    $_FILES['files']['tmp_name'][$num]['upload'], $file->getFullPath()
                );
                $command = $vc->getCommand('add')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bCommit = true;
            } else if ($bNew || (isset($arFile['content']) && $file->getContent() != $arFile['content'])) {
                file_put_contents($file->getFullPath(), $arFile['content']);
                $command = $vc->getCommand('add')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bCommit = true;
            }
        }

        $commitmsg = "phorkie commit";
        if (isset($sessionData['identity'])) {
            $commitmsg .= " from ".$sessionData['identity'];
        } else {
            $commitmsg .= " by ".$sessionData['ipaddr'];
        }

        if ($bCommit) {
            $vc->getCommand('commit')
                ->setOption('message', $commitmsg)
                ->setOption('author', $sessionData['name'].' <'.$sessionData['email'].'>')
                ->execute();
            $bChanged = true;
        }

        if ($bChanged) {
            //FIXME: index changed files only
            //also handle file deletions
            $db = new Database();
            if ($bNew) {
                $db->getIndexer()->addRepo($this->repo);
            } else {
                $db->getIndexer()->updateRepo($this->repo);
            }
        }

        return true;
    }

    protected function hasContent($postData)
    {
        foreach ($postData['files'] as $num => $arFile) {
            if ($_FILES['files']['error'][$num]['upload'] == 0) {
                return true;
            }
            if ($arFile['content'] != '') {
                return true;
            }
        }
        return false;
    }

    public function createRepo()
    {
        $rs = new Repositories();
        $repo = $rs->createNew();
        $vc = $repo->getVc();
        $vc->getCommand('init')
            //this should be setOption, but it fails with a = between name and value
            ->addArgument('--separate-git-dir')
            ->addArgument($GLOBALS['phorkie']['cfg']['gitdir'] . '/' . $repo->id . '.git')
            ->addArgument($repo->workDir)
            ->execute();

        foreach (glob($repo->gitDir . '/hooks/*') as $hookfile) {
            unlink($hookfile);
        }

        touch($repo->gitDir . '/git-daemon-export-ok');

        return $repo;
    }

    public function getNextNumberedFile($prefix)
    {
        $num = -1;
        do {
            ++$num;
            $files = glob($this->repo->workDir . '/' . $prefix . $num . '.*');
        } while (count($files));

        return $prefix . $num;
    }

    protected function getType($content)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'phorkie-autodetect-');
        file_put_contents($tmp, $content);
        $type = \MIME_Type_PlainDetect::autoDetect($tmp);
        unlink($tmp);

        return $this->findExtForType($type);
    }

    protected function findExtForType($type)
    {
        $ext = 'txt';
        foreach ($GLOBALS['phorkie']['languages'] as $lext => $arLang) {
            if ($arLang['mime'] == $type) {
                $ext = $lext;
                break;
            }
        }
        return $ext;
    }
}

?>
