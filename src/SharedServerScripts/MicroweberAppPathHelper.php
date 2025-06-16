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
        return $this->fileManager->isLink($this->path . '/vendor');
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
        $configFile = $this->path . '/.env';
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
        $modulesPath = $this->path . '/userfiles/modules';

        if (!$this->fileManager->isDir($modulesPath)) {

            $modulesPath = $this->path . '/Modules';
        }

        if (!$this->fileManager->isDir($modulesPath)) {
            return [];
        }


        $scan = $this->fileManager->scanDir($modulesPath);
        if (!empty($scan)) {
            foreach ($scan as $dir) {
                if ($dir == '.' || $dir == '..') {
                    continue;
                }
                $name = $dir;
                $fullDir = $modulesPath . '/' . $dir;
                $moduleConfig = $fullDir . '/composer.json';
                $moduleVersion = null;

                if ($this->fileManager->fileExists($moduleConfig)) {

                    $moduleConfigContent = $this->fileManager->fileGetContents($moduleConfig);
                    $moduleConfigData = json_decode($moduleConfigContent, true);

                    if (isset($moduleConfigData['version'])) {
                        $moduleVersion = $moduleConfigData['version'];
                    }

                    if (isset($moduleConfigData['name'])) {
                        $name = $moduleConfigData['name'];
                    }
                    if (isset($moduleConfigData['target-dir'])) {
                        $dir = $moduleConfigData['target-dir'];
                    }
                } else {
                    $moduleVersion = 'unknown';

                }
                $modules[] = [
                    'name' => $name,
                    'targetDir' => $dir,
                    'version' => $moduleVersion,
                ];
            }
        }
        if (empty($modules)) {
            return [];
        }
        // Sort modules by name
        usort($modules, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        return $modules;

    }

    /**
     * @return array
     */
    public function getSupportedTemplates()
    {


        $templates = [];
        $templatePath = $this->path . '/userfiles/templates';

        if (!$this->fileManager->isDir($templatePath)) {
            $templatePath = $this->path . '/Templates';
        }

        if (!$this->fileManager->isDir($templatePath)) {
            return [];
        }


        $scan = $this->fileManager->scanDir($templatePath);
        if (!empty($scan)) {
            foreach ($scan as $dir) {
                if ($dir == '.' || $dir == '..') {
                    continue;
                }
                $name = $dir;
                $fullDir = $templatePath . '/' . $dir;
                $templateConfig = $fullDir . '/composer.json';
                $templateVersion = null;

                if ($this->fileManager->fileExists($templateConfig)) {

                    $templateConfigContent = $this->fileManager->fileGetContents($templateConfig);
                    $templateConfigData = json_decode($templateConfigContent, true);

                    if (isset($templateConfigData['version'])) {
                        $templateVersion = $templateConfigData['version'];
                    }

                    if (isset($templateConfigData['name'])) {
                        $name = $templateConfigData['name'];
                    }
                    if (isset($templateConfigData['target-dir'])) {
                        $dir = $templateConfigData['target-dir'];
                    }
                } else {
                    $templateVersion = 'unknown';

                }
                $templates[] = [
                    'name' => $name,
                    'targetDir' => $dir,
                    'version' => $templateVersion,
                ];
            }
        }

        return $templates;


    }

    /**
     * @return array
     */
    public function getSupportedLanguages()
    {

        $langDir = $this->path . '/src/MicroweberPackages/Translation/resources/lang';
        if (!$this->fileManager->isDir($langDir)) {
            return [];
        }
        $languages = [
            'en_US' => 'EN_US',
            'bg_BG' => 'BG_BG',
            'ar_SA' => 'AR_SA'
        ];
        $scan = $this->fileManager->scanDir($langDir);
        if (!empty($scan)) {
            foreach ($scan as $dir) {
                if ($dir == '.' || $dir == '..') {
                    continue;
                }
                $languageAbr = str_replace('.json', false, $dir);
                $upperText = strtoupper($languageAbr);
                $languages[trim($languageAbr)] = $upperText;
            }
        }

        return $languages;

//        try {
//            $executeArtisan = $this->shellExecutor->executeCommand([
//                'php',
//                '-d memory_limit=512M',
//                $this->path . '/artisan',
//                'microweber:get-languages',
//            ]);
//
//            $decodeArtisanOutput = json_decode($executeArtisan, true);
//            if (!empty($decodeArtisanOutput)) {
//                return $decodeArtisanOutput;
//            }
//        } catch (\Exception $e) {
//
//        }

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

    public function getAppDetails()
    {
        try {
            $executeArtisan = $this->shellExecutor->executeCommand([
                'php',
                '-d memory_limit=512M',
                $this->path . '/artisan',
                'microweber:get-app-details',
            ]);
            $decodeArtisanOutput = json_decode($executeArtisan, true);
            if (!empty($decodeArtisanOutput)) {
                return $decodeArtisanOutput;
            }
        } catch (\Exception $e) {

        }
        return false;
    }

    /**
     * @return false|string
     */
    public function generateAdminLoginToken()
    {
        try {
            $token = $this->shellExecutor->executeCommand([
                'php',
                '-d memory_limit=512M',
                $this->path . '/artisan',
                'microweber:generate-admin-login-token'
            ]);

            return $token;

        } catch (\Exception $e) {
            // error
        }

        return false;
    }

}
