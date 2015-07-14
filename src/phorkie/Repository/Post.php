<?php
namespace phorkie;

class Repository_Post
{
    public $repo;

    /**
     * When a new file is created during processing, its name
     * is stored here for later use.
     *
     * @var string
     */
    public $newfileName;

    /**
     * List of files that have been renamed.
     *
     * @var array
     */
    public $renameMap = array();


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

        $this->renameMap   = array();
        $this->newfileName = null;

        foreach ($postData['files'] as $num => $arFile) {
            $bUpload = false;
            if ($_FILES['files']['error'][$num]['upload'] == 0) {
                //valid file upload
                $bUpload = true;
            } else if ($arFile['content'] == '' && $arFile['name'] == '') {
                //empty (new) file
                continue;
            }

            $originalName = Tools::sanitizeFilename($arFile['original_name']);
            $name         = Tools::sanitizeFilename($arFile['name']);

            if ($arFile['type'] == '_auto_') {
                //FIXME: upload
                $arFile['type'] = $this->getType($arFile['content']);
            }

            if ($name == '') {
                if ($bUpload) {
                    $name = Tools::sanitizeFilename(
                        $_FILES['files']['name'][$num]['upload']
                    );
                } else {
                    $name = $this->getNextNumberedFile('phork')
                        . '.' . $arFile['type'];
                }
            }

            $bNew = false;
            $bDelete = false;
            if (!isset($originalName) || $originalName == '') {
                //new file
                $bNew = true;
                if (strpos($name, '.') === false) {
                    //automatically append file extension if none is there
                    $name .= '.' . $arFile['type'];
                }
                $this->newfileName = $name;
            } else if (!$this->repo->hasFile($originalName)) {
                //unknown file
                //FIXME: Show error message
                continue;
            } else if (isset($arFile['delete']) && $arFile['delete'] == 1) {
                $bDelete = true;
            } else if ($originalName != $name) {
                if (strpos($name, '/') === false) {
                    //ignore names with a slash in it, would be new directory
                    //FIXME: what to do with overwrites?
                    $vc->getCommand('mv')
                        ->addArgument($originalName)
                        ->addArgument($name)
                        ->execute();
                    $bCommit = true;
                    $this->renameMap[$originalName] = $name;
                } else {
                    $name = $originalName;
                }
            }

            $file = $this->repo->getFileByName($name, false);
            if ($originalName !== '') {
                $originalFile = $this->repo->getFileByName($originalName, false);
            }
            if ($bDelete) {
                $command = $vc->getCommand('rm')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bCommit = true;
            } else if ($bUpload) {
                move_uploaded_file(
                    $_FILES['files']['tmp_name'][$num]['upload'],
                    $file->getFullPath()
                );
                $command = $vc->getCommand('add')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bCommit = true;
            } else if ($bNew
                || (isset($arFile['content']) && isset($originalFile)
                    && $originalFile->getContent() != $arFile['content']
                )
            ) {
                $dir = dirname($file->getFullPath());
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($file->getFullPath(), $arFile['content']);
                $command = $vc->getCommand('add')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bCommit = true;
            }
        }

        if (isset($sessionData['identity'])) {
            $notes = $sessionData['identity'];
        } else {
            $notes = $sessionData['ipaddr'];
        }

        if ($bCommit) {
            $vc->getCommand('commit')
                ->setOption('message', '')
                ->setOption('allow-empty-message')
                ->setOption('no-edit')
                ->setOption(
                    'author',
                    $sessionData['name'] . ' <' . $sessionData['email'] . '>'
                )
                ->execute();
            //FIXME: git needs ref BEFORE add
            //quick hack until http://pear.php.net/bugs/bug.php?id=19605 is fixed
            //also waiting for https://pear.php.net/bugs/bug.php?id=19623
            $vc->getCommand('notes --ref=identity add')
                ->setOption('force')
                ->setOption('message', "$notes")
                ->execute();
            //update info for dumb git HTTP transport
            //the post-update hook should do that IMO, but does not somehow
            $vc->getCommand('update-server-info')->execute();

            //we changed the hash by committing, so reload it
            $this->repo->reloadHash();

            $bChanged = true;
        }

        if ($bChanged) {
            //FIXME: index changed files only
            //also handle file deletions
            $db = new Database();
            $not = new Notificator();
            if ($bNew) {
                $db->getIndexer()->addRepo($this->repo);
                $not->create($this->repo);
            } else {
                $commits = $this->repo->getHistory();
                $db->getIndexer()->updateRepo(
                    $this->repo,
                    $commits[count($commits)-1]->committerTime,
                    $commits[0]->committerTime
                );
                $not->edit($this->repo);
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
            if (isset($arFile['content']) && $arFile['content'] != '') {
                return true;
            }
            if (isset($arFile['name']) && $arFile['name'] != '') {
                //binary files do not have content
                return true;
            }
            if (isset($arFile['delete']) && $arFile['delete'] != '') {
                //binary files do not have content
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
            ->addArgument(
                $GLOBALS['phorkie']['cfg']['gitdir'] . '/' . $repo->id . '.git'
            )
            ->addArgument($repo->workDir)
            ->execute();

        $rs = new Repository_Setup($repo);
        $rs->afterInit();

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

    public function getType($content, $returnError = false)
    {
        if (getenv('PATH') == '') {
            //php-fpm does not fill $PATH by default
            // we have to work around that since System::which() uses it
            putenv('PATH=/usr/local/bin:/usr/bin:/bin');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'phorkie-autodetect-');
        file_put_contents($tmp, $content);
        $type = Tool_MIME_Type_PlainDetect::autoDetect($tmp);
        unlink($tmp);

        if ($returnError && $type instanceof \PEAR_Error) {
            return $type;
        }

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
