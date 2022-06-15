<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;

class MicroweberInstallationsRecursiveScanner
{
    public $path;

    /**
     * @var NativeFileManager
     */
    public $fileManager;

    /**
     * @param $fileManagerAdapter
     */
    public function __construct()
    {
        $this->fileManager = new NativeFileManager();
    }

    /**
     * @param $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function scan()
    {
        $installations = [];

        $scanedFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path));
        foreach ($scanedFiles as $file) {

            if (!$file->isFile()) {
                continue;
            }

            $skip = true;
            if (strpos($file->getPathname(), '/config/microweber.php') !== false) {
                $skip = false;
            }
            if ($skip) {
                continue;
            }

            $scanPath = dirname(dirname($file->getPathname())) . '/';

            $sharedPathHelper = new MicroweberAppPathHelper();
            $sharedPathHelper->setPath($scanPath);
            $createdAt = $sharedPathHelper->getCreatedAt();

            if (!$createdAt) {
                continue;
            }

            $installations[] = [
                'path'=>$scanPath,
                'is_symlink'=>$sharedPathHelper->isSymlink(),
                'version'=>$sharedPathHelper->getCurrentVersion(),
                'installed'=>$sharedPathHelper->isInstalled(),
                'installed_templates'=>$sharedPathHelper->getSupportedTemplates(),
                'installed_languages'=>$sharedPathHelper->getSupportedLanguages(),
                'created_at'=>$createdAt
            ];
        }

        return $installations;
    }

}
