<?php
namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;

class MicroweberReinstaller extends MicroweberInstaller {

    public function run()
    {
        if ($this->type == self::TYPE_SYMLINK) {
            return $this->runSymlinkReinstall();
        }

        if ($this->type == self::TYPE_STANDALONE) {
            return $this->runStandaloneReinstall();
        }
    }


    public function runSymlinkReinstall()
    {
        $this->enableChownAfterInstall();

        foreach ($this->_getFilesForSymlinking() as $fileOrFolder) {

            $sourceDirOrFile = $this->sourcePath . '/' . $fileOrFolder;
            $targetDirOrFile = $this->path . '/' . $fileOrFolder;

            // Skip symlinked file
            if ($this->fileManager->isLink($targetDirOrFile)) {
                continue;
            }

            if ($this->fileManager->isDir($targetDirOrFile)) {
                $this->fileManager->rmdirRecursive($targetDirOrFile);
            }

            // Create symlink
            $this->fileManager->symlink($sourceDirOrFile, $targetDirOrFile);
        }

        $this->_chownFolders();
    }

    public function runStandaloneReinstall()
    {
        $this->enableChownAfterInstall();

        foreach ($this->_getFilesForSymlinking() as $fileOrFolder) {

            $sourceDirOrFile = $this->sourcePath . '/' . $fileOrFolder;
            $targetDirOrFile = $this->path . '/' . $fileOrFolder;

            // Delete
            if ($this->fileManager->isDir($sourceDirOrFile)) {
                if ($this->fileManager->isDir($targetDirOrFile)) {
                    $this->fileManager->rmdirRecursive($targetDirOrFile);
                }

                $this->fileManager->copyFolder($sourceDirOrFile, $targetDirOrFile);
            }

        }

        $this->_chownFolders();
    }
}
