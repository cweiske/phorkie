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
    public function process($postData)
    {
        if (!isset($postData['files'])) {
            return false;
        }

        if (!$this->repo) {
            $this->repo = $this->createRepo();
        }

        $vc = $this->repo->getVc();
        $this->repo->setDescription($postData['description']);

        $bChanged = false;
        foreach ($postData['files'] as $num => $arFile) {
            $bUpload = false;
            if ($_FILES['files']['error'][$num]['upload'] == 0) {
                //valid file upload
                $bUpload = true;
            } else if ($arFile['content'] == '' && $arFile['name'] == '') {
                //empty (new) file
                continue;
            }

            $orignalName = $this->sanitizeFilename($arFile['original_name']);
            $name        = $this->sanitizeFilename($arFile['name']);

            if ($name == '') {
                if ($bUpload) {
                    $name = $this->sanitizeFilename($_FILES['files']['name'][$num]['upload']);
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
                //FIXME: what to do with overwrites?
                $vc->getCommand('mv')
                    ->addArgument($orignalName)
                    ->addArgument($name)
                    ->execute();
                $bChanged = true;
            }

            $file = $this->repo->getFileByName($name, false);
            if ($bDelete) {
                $command = $vc->getCommand('rm')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bChanged = true;
            } else if ($bUpload) {
                move_uploaded_file(
                    $_FILES['files']['tmp_name'][$num]['upload'], $file->getPath()
                );
                $command = $vc->getCommand('add')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bChanged = true;
            } else if ($bNew || $file->getContent() != $arFile['content']) {
                file_put_contents($file->getPath(), $arFile['content']);
                $command = $vc->getCommand('add')
                    ->addArgument($file->getFilename())
                    ->execute();
                $bChanged = true;
            }
        }

        if ($bChanged) {
            $vc->getCommand('commit')
                ->setOption('message', '')
                ->setOption('allow-empty-message')
                ->setOption('author', 'Anonymous <anonymous@phorkie>')
                ->execute();
        }

        return true;
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
            ->addArgument($repo->workDir);
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

    /**
     * Removes malicious parts from a file name
     *
     * @param string $file File name from the user
     *
     * @return string Fixed and probably secure filename
     */
    public function sanitizeFilename($file)
    {
        $file = trim($file);
        $file = str_replace(array('\\', '//'), '/', $file);
        $file = str_replace('/../', '/', $file);
        if (substr($file, 0, 3) == '../') {
            $file = substr($file, 3);
        }
        if (substr($file, 0, 1) == '../') {
            $file = substr($file, 1);
        }

        return $file;
    }
}

?>
