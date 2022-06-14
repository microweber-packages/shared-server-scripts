<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;

class MicroweberSharedPathHelper
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

    public function getLastDownloadDate()
    {
        $versionFile = $this->fileManager->fileExists($this->path . '/version.txt');

        $date = 'unknown';
        if ($versionFile) {
            $date = date('Y-m-d- H:i:s', $this->fileManager->filemtime($this->path . '/version.txt'));
        }

        return $date;
    }

    /**
     * @return array
     */
    public function getSupportedTemplates()
    {
        $templates = [];
        $templatesPath = $this->path . '/userfiles/templates/';

        if ($this->fileManager->fileExists($templatesPath)) {
            $listDir = $this->fileManager->scanDir($templatesPath, true);
            foreach ($listDir as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $upperText = $file;
                $upperText = ucfirst($upperText);

                // Read config from template path
                $config = false;
                $sourceTemplateVersion = false;
                $sourceTemplateConfig = false;
                // Check for config file
                $templateFolderPathConfig = $templatesPath . $file.DIRECTORY_SEPARATOR.'config.php';
                if (is_file($templateFolderPathConfig)) {
                    include $templateFolderPathConfig;
                    $sourceTemplateConfig = $config;
                }
                if (isset($sourceTemplateConfig['version'])) {
                    $sourceTemplateVersion = $sourceTemplateConfig['version'];
                }

                $templates[] = [
                    'targetDir' => trim($file),
                    'version' => $sourceTemplateVersion,
                    'name' => $upperText
                ];
            }
        } else {
            $templates[] = [
                'targetDir' => 'default',
                'version' => '0.0',
                'name' => 'Default'
            ];
        }

        asort($templates);

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
