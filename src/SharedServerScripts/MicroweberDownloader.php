<?php
namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\DefaultFileAdapter;
use MicroweberPackages\SharedServerScripts\Interfaces\IMicroweberDownloader;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\DefaultShellAdapter;
use MicroweberPackages\SharedServerScripts\Shell\ShellExecutor;

class MicroweberDownloader implements IMicroweberDownloader {

    public $fileManager;
    public $shellExecutor;
    public $realeaseSource = 'dev';

    /**
     * @param $fileManagerAdapter
     * @param $shellExecutorAdapter
     */
    public function __construct() {
        $this->fileManager = new DefaultFileAdapter();
        $this->shellExecutor = new DefaultShellAdapter();
    }

    public function setFileManager($adapter)
    {
        $this->fileManager = $adapter;
    }

    /**
     * @param $source
     * @return void
     */
    public function setReleaseSource($source)
    {
        $this->realeaseSource = $source;
    }

    /**
     * @param string $target
     * @return void
     */
    public function download(string $target)
    {
        // Validate target path
        if (!$this->fileManager->isDir(dirname($target))) {
            throw new \Exception('Parent folder of target path is not valid.');
        }

        if (!$this->fileManager->isWritable(dirname($target))) {
            throw new \Exception('Parent folder of target path is not writable.');
        }

        // Get latest release of app
        $release = $this->getRelease();
        if (empty($release)) {
            throw new \Exception('No releases found.');
        }


        // Download the app
        $status = $this->downloadMainApp($release['url'], $target);

        // Validate app installation
        $mainAppDownloadingErrors = [];
        if ($this->fileManager->isDir($target)) {
            $mainAppDownloadingErrors[] = true;
        }
        if ($this->fileManager->isFile($target . DIRECTORY_SEPARATOR . 'index.php')) {
            $mainAppDownloadingErrors[] = true;
        }
        if (!empty($mainAppDownloadingErrors)) {
            throw new \Exception('Error when downloading the main app.');
        }

        return ['downloaded'=>true];
    }

    /**
     * @param $url
     * @param $target
     * @return string
     */
    public function downloadMainApp($url, $target)
    {
        $status = $this->shellExecutor->executeFile(dirname(dirname(__DIR__))
            . DIRECTORY_SEPARATOR . 'shell-scripts'
            . DIRECTORY_SEPARATOR . 'unzip_app_version.sh', [base64_encode($url), $target]);

        return $status;
    }

    /**
     * @return string[]
     */
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
