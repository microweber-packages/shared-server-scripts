<?php
namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberWhitelabelSettingsUpdater {

    use MicroweberFileOperationsTrait;

    public function __construct() {
        $this->initializeAdapters();
    }

    public function apply($settings)
    {
        $whitelabelJson = json_encode($settings, JSON_PRETTY_PRINT);

        $whmFilePath = $this->path . '/storage/';
        $whmFileName = 'branding_saas.json';

        if (!$this->fileManager->isDir($whmFilePath)) {
            $this->fileManager->mkdir($whmFilePath, null, true);
        }

        return $this->fileManager->filePutContents($whmFilePath . $whmFileName, $whitelabelJson);
    }

}
