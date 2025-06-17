<?php
namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberWhitelabelWebsiteApply {

    use MicroweberFileOperationsTrait;

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
        $this->initializeAdapters();
    }

    public function apply()
    {
        $brandingSourceFile = $this->sharedPath . '/storage/branding_saas.json';
        $brandingTargetFile = $this->webPath . '/storage/branding_saas.json';

        if ($this->fileManager->fileExists($brandingTargetFile)) {
            $this->fileManager->unlink($brandingTargetFile);
        }

        $this->fileManager->symlink($brandingSourceFile, $brandingTargetFile);
    }

}
