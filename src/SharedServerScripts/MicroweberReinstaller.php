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

        foreach ($this->getFilesForSymlinking() as $fileOrFolder) {

            $sourceDirOrFile = $this->sourcePath . '/' . $fileOrFolder;
            $targetDirOrFile = $this->path . '/' . $fileOrFolder;

            // Skip symlinked file
            if ($this->fileManager->isLink($targetDirOrFile)) {
                continue;
            }

            $this->removeFileOrFolder($targetDirOrFile);

            // Create symlink
            $this->fileManager->symlink($sourceDirOrFile, $targetDirOrFile);
        }

        $this->addMissingConfigFiles();

        $this->_chownFolders();
    }

    public function runStandaloneReinstall()
    {
        $this->enableChownAfterInstall();

        foreach ($this->getFilesForSymlinking() as $fileOrFolder) {
            $sourceDirOrFile = $this->sourcePath . '/' . $fileOrFolder;

            if(!$this->sourceExists($sourceDirOrFile)) {
                continue; // Skip if source is not a directory or file
            }

            $targetDirOrFile = $this->path . '/' . $fileOrFolder;

            // Remove existing target
            $this->removeFileOrFolder($targetDirOrFile);

            // Copy from source
            if ($this->fileManager->isDir($sourceDirOrFile)) {
                $this->fileManager->copyFolder($sourceDirOrFile, $targetDirOrFile);
            }
        }

        // And then we will copy files
        foreach ($this->getFilesForCopy() as $file) {
            $sourceDirOrFile = $this->sourcePath .'/'. $file;
            $targetDirOrFile = $this->path .'/'. $file;

            if(!$this->sourceExists($sourceDirOrFile)) {
                continue; // Skip if source is not a directory or file
            }

            if ($this->fileManager->isFile($targetDirOrFile)) {
                unlink($targetDirOrFile);
            }

            $this->fileManager->copy($sourceDirOrFile, $targetDirOrFile);
        }

        $this->addMissingConfigFiles();

        $this->_chownFolders();
    }



}
