<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\Interfaces\IMicroweberDownloader;
use MicroweberPackages\SharedServerScripts\Shell\ShellExecutor;

class MicroweberDownloader implements IMicroweberDownloader {

    public $realeaseSource = 'dev';

    public function setReleaseSource($source)
    {
        $this->realeaseSource = $source;
    }

    public function download(string $target)
    {
        if (!is_dir($target)) {
            throw new Exception('Target path is not valid.');
        }
        if (!is_writable($target)) {
            throw new Exception('Target path is not writable.');
        }

        $release = $this->getRelease();
        if (empty($release)) {
            throw new Exception('No releases found.');
        }

        $mainAppDownloadingErrors = [];
        $status = $this->downloadMainApp($release['url'], $target);
        if (is_dir($target)) {
            $mainAppDownloadingErrors[] = true;
        }
        if (is_file($target . DIRECTORY_SEPARATOR . 'index.php')) {
            $mainAppDownloadingErrors[] = true;
        }
        if (!empty($mainAppDownloadingErrors)) {
            throw new Exception('Error when downloading the main app.');
        }
        
    }

    public function downloadMainApp($url, $target)
    {
        $shellExecutor = new ShellExecutor();
        $status = $shellExecutor->executeFile(dirname(dirname(__DIR__))
            . DIRECTORY_SEPARATOR . 'shell-scripts'
            . DIRECTORY_SEPARATOR . 'unzip_app_version.sh', [base64_encode($url), $target]);

        return $status;
    }

    public function getRelease()
    {
        if ($this->realeaseSource == 'dev') {

            $branch = 'dev';

            return [
                'version'=>'Latest development version',
                'composer_url'=>'http://updater.microweberapi.com/builds/'.$branch.'/composer.json',
                'version_url'=>'http://updater.microweberapi.com/builds/'.$branch.'/version.txt',
                'url'=>'http://updater.microweberapi.com/builds/'.$branch.'/microweber.zip'
            ];
        }

        return [
            'version'=>'Latest production version',
            'composer_url'=>'http://updater.microweberapi.com/builds/master/composer.json',
            'version_url'=>'http://updater.microweberapi.com/builds/master/version.txt',
            'url'=>'http://updater.microweberapi.com/builds/master/microweber.zip'
        ];
    }

}
