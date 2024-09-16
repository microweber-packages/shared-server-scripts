<?php
namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberWhitelabelWebsiteApply {

    /**
     * Web path of Website
     * @var
     */
    public $webPath;

    /**
     * Shared path of Microweber
     * @var
     */
    public $sharedPath;

    /**
     * @param $adapter
     * @return void
     */
    public function setFileManager($adapter)
    {
        $this->fileManager = $adapter;
    }

    /**
     * @param $adapter
     * @return void
     */
    public function setShellExecutor($adapter)
    {
        $this->shellExecutor = $adapter;
    }

    /**
     * @param $path
     * @return void
     */
    public function setWebPath($path)
    {
        $this->webPath = $path;
    }

    /**
     * @param $path
     *
     * @return void
     */
    public function setSharedPath($path)
    {
        $this->sharedPath = $path;
    }

    public function __construct() {
        $this->fileManager = new NativeFileManager();
        $this->shellExecutor = new NativeShellExecutor();
    }

    public function apply()
    {
        $brandingSourceFile = $this->sharedPath . '/storage/branding_saas.json';
        $brandingTargetFile = $this->webPath . '/storage/branding_saas.json';
        $brandingSecondTargetFile = $this->webPath . '/storage/branding.json';

        if ($this->fileManager->fileExists($brandingTargetFile)) {
            $this->fileManager->unlink($brandingTargetFile);
        }
        if ($this->fileManager->fileExists($brandingSecondTargetFile)) {
            $this->fileManager->unlink($brandingSecondTargetFile);
        }

        $this->fileManager->symlink($brandingSourceFile, $brandingTargetFile);
        $this->fileManager->symlink($brandingSourceFile, $brandingSecondTargetFile);
    }

}
