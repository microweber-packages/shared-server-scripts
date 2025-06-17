<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;

class MicroweberUninstaller {

    use MicroweberFileOperationsTrait;

    public function __construct()
    {
        $this->initializeAdapters();
    }


    public function run()
    {
        $deletedFiles = [];
        $errors = [];

        // Delete files
        $files = $this->getFilesForDelete();
        foreach ($files as $file) {
            $deleteFile = $this->path . DIRECTORY_SEPARATOR . $file;
            try {
                if ($this->fileManager->isFile($deleteFile)) {
                    $this->fileManager->unlink($deleteFile);
                    $deletedFiles[] = $file;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to delete file {$file}: " . $e->getMessage();
            }
        }

        // Delete directories
        $dirs = $this->getDirsForDelete();
        foreach ($dirs as $dir) {
            $deleteDir = $this->path . DIRECTORY_SEPARATOR . $dir;
            try {
                if ($this->fileManager->isDir($deleteDir)) {
                    if ($this->fileManager->isLink($deleteDir)) {
                        $this->fileManager->unlink($deleteDir);
                    } else {
                        $this->fileManager->rmdirRecursive($deleteDir);
                    }
                    $deletedFiles[] = $dir;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to delete directory {$dir}: " . $e->getMessage();
            }
        }

        return $this->buildResult(
            empty($errors), 
            empty($errors) ? 'Uninstallation completed successfully' : 'Uninstallation completed with errors',
            [
                'done' => true,
                'deleted' => $deletedFiles,
                'errors' => $errors
            ]
        );
    }

    /**
     * Get directories that need to be deleted during uninstallation
     * 
     * @return array
     */
    private function getDirsForDelete() {

        $dirs = [];
        $dirs[] = 'bootstrap';
        $dirs[] = 'vendor';
        $dirs[] = 'config';
        $dirs[] = 'database';
        $dirs[] = 'resources';
        $dirs[] = 'src';
        $dirs[] = 'storage';
        $dirs[] = 'userfiles';

        return $dirs;
    }

    /**
     * Get files that need to be deleted during uninstallation
     * 
     * @return array
     */
    private function getFilesForDelete() {

        $files = [];
        $files[] = 'version.txt';
        $files[] = 'phpunit.xml';
        $files[] = 'index.php';
        $files[] = '.htaccess';
        $files[] = 'favicon.ico';
        $files[] = 'composer.json';
        $files[] = 'artisan';

        return $files;
    }

}
