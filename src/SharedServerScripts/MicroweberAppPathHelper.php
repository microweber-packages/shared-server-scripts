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
     * @param $adapter
     * @return void
     */
    public function setFileManager($adapter)
    {
        $this->fileManager = $adapter;
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
        try {
            $executeArtisan = $this->shellExecutor->executeCommand([
                'php',
                '-d memory_limit=512M',
                $this->path . '/artisan',
                'microweber:get-modules',
            ]);
            $decodeArtisanOutput = json_decode($executeArtisan, true);
            if (!empty($decodeArtisanOutput)) {
                return $decodeArtisanOutput;
            }
        } catch (\Exception $e) {

        }

        return [];
    }

    /**
     * @return array
     */
    public function getSupportedTemplates()
    {
        try {
            $executeArtisan = $this->shellExecutor->executeCommand([
                'php',
                '-d memory_limit=512M',
                $this->path . '/artisan',
                'microweber:get-templates',
            ]);

            $decodeArtisanOutput = json_decode($executeArtisan, true);
            if (!empty($decodeArtisanOutput)) {
                return $decodeArtisanOutput;
            }
        } catch (\Exception $e) {

        }

        return [];
    }

    /**
     * @return array
     */
    public function getSupportedLanguages()
    {
        try {
            $executeArtisan = $this->shellExecutor->executeCommand([
                'php',
                '-d memory_limit=512M',
                $this->path . '/artisan',
                'microweber:get-languages',
            ]);

            $decodeArtisanOutput = json_decode($executeArtisan, true);
            if (!empty($decodeArtisanOutput)) {
                return $decodeArtisanOutput;
            }
        } catch (\Exception $e) {

        }

        return [];
    }

    /**
     * @return false|string
     */
    public function enableAdminLoginWithToken()
    {
        try {
              $executeArtisan = $this->shellExecutor->executeCommand([
                  'php',
                  '-d memory_limit=512M',
                  $this->path . '/artisan',
                  'microweber:module',
                  'login-with-token',
                  '1',
              ]);

            return $executeArtisan;

        } catch (\Exception $e) {

        }

        return false;
    }

    /**
     * @return false|string
     */
    public function loginAsAdmin()
    {
        try {
            $autoLoginUrl = $this->shellExecutor->executeCommand([
                'php',
                '-d memory_limit=512M',
                $this->path . '/artisan',
                'microweber:generate-admin-login-token-url'
            ]);

            return $autoLoginUrl;

        } catch (\Exception $e) {

        }

        return false;
    }

}
