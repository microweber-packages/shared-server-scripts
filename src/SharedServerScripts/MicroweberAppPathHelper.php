<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberAppPathHelper
{
    /**
     * @var
     */
    public $path;

    /**
     * @var NativeFileManager
     */
    public $fileManager;

    /**
     * @var NativeShellExecutor
     */
    public $shellExecutor;

    public function __construct()
    {
        $this->fileManager = new NativeFileManager();
        $this->shellExecutor = new NativeShellExecutor();
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
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function isSymlink()
    {
        return $this->fileManager->isLink($this->path.'/vendor');
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        $configFile = $this->path . '/config/microweber.php';
        if ($this->fileManager->fileExists($configFile)) {
            return true;
        }

        return false;
    }

    /**
     * @return false|string
     */
    public function getCreatedAt()
    {
        $configFile = $this->path . '/config/app.php';
        if ($this->fileManager->fileExists($configFile)) {
            return date("Y-m-d H:i:s", $this->fileManager->filectime($configFile));
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        $versionFile = $this->fileManager->fileExists($this->path . '/version.txt');

        $version = 'unknown';
        if ($versionFile) {
            $version = $this->fileManager->fileGetContents($this->path . '/version.txt');
            $version = strip_tags($version);
        }

        return $version;
    }

    /**
     * @return array
     */
    public function getSupportedModules()
    {
        $modules = [];
        $modulesPath = $this->path . '/userfiles/modules/';

        if ($this->fileManager->fileExists($modulesPath)) {
            $listDir = $this->fileManager->scanDir($modulesPath, true);
            foreach ($listDir as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $upperText = $file;
                $upperText = ucfirst($upperText);

                // Read config from template path
                $config = false;
                $sourceModuleVersion = false;
                $sourceModuleConfig = false;
                // Check for config file
                $moduleFolderPathConfig = $modulesPath . $file.DIRECTORY_SEPARATOR.'config.php';
                if (is_file($moduleFolderPathConfig)) {
                    include $moduleFolderPathConfig;
                    $sourceModuleConfig = $config;
                }
                if (isset($sourceModuleConfig['version'])) {
                    $sourceModuleVersion = $sourceModuleConfig['version'];
                }

                $modules[] = [
                    'targetDir' => trim($file),
                    'version' => $sourceModuleVersion,
                    'name' => $upperText
                ];
            }
        }

        asort($modules);

        return $modules;
    }

    /**
     * @return array
     */
    public function getSupportedTemplates()
    {

        $templates = [];

        $executeArtisan = $this->shellExecutor->executeCommand([
            'php',
            '-d memory_limit=512M',
            $this->path . '/artisan',
            'microweber:reload_database',
        ]);

        $executeArtisan = $this->shellExecutor->executeCommand([
            'php',
            '-d memory_limit=512M',
            $this->path . '/artisan',
            'microweber:get-templates',
        ]);

        dd($executeArtisan);

        return $templates;
    }

    /**
     * @return array
     */
    public function getSupportedLanguages()
    {
        $languages = [];

        $languagesPath = $this->path . '/userfiles/modules/microweber/language';

        if ($this->fileManager->fileExists($languagesPath)) {
            $listDir = $this->fileManager->scandir($languagesPath, true);
            foreach ($listDir as $file) {
                $ext = $this->fileManager->fileExtension($file);
                if ($ext == 'json') {

                    $upperText = str_replace('.json', false, $file);
                    $upperText = strtoupper($upperText);

                    $languages[trim(strtolower($upperText))] = $upperText;
                }
            }
        } else {
            $languages['en'] = 'EN';
        }

        asort($languages);
        return $languages;
    }


}
