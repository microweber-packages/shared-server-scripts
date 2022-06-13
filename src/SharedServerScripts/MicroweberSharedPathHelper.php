<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

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
    public function __construct() {
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

    /**
     * @return array
     */
    public function getSupportedTemplates()
    {
        $templates = [];
        $templatesPath = $this->path . 'userfiles/templates/';

        if ($this->fileManager->fileExists($templatesPath)) {
            $listDir = $this->fileManager->scanDir($templatesPath, true);
            foreach ($listDir as $file) {
                $upperText = $file;
                $upperText = ucfirst($upperText);
                $templates[trim($file)] = $upperText;
            }
        } else {
            $templates['Default'] = 'Default';
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

        $languagesPath = $this->path . 'userfiles/modules/microweber/language';

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
