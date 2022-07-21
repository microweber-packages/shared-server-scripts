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
        foreach ($this->_getFilesForSymlinking() as $fileOrFolder) {

            $sourceDirOrFile = $this->sourcePath . '/' . $fileOrFolder;
            $targetDirOrFile = $this->path . '/' . $fileOrFolder;

            // Delete
            if ($this->fileManager->isLink($targetDirOrFile)) {
                $this->fileManager->unlink($targetDirOrFile);
            }

            // Create symlink
            $this->fileManager->symlink($sourceDirOrFile, $targetDirOrFile);
        }

        $this->_chownFolders();
    }

    public function runStandaloneReinstall()
    {

    }
}
