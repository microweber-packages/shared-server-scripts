<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\PleskServerFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\PleskShellExecutor;
use PHPUnit\Framework\TestCase;
use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberDownloaderTest extends TestCase
{
    public function testDownload()
    {
        mkdir(dirname(__DIR__).'/temp');

        $downloadTargetPath = dirname(__DIR__).'/temp/microweber-latest/';

        $downloader = new MicroweberDownloader();
        $downloader->setFileManager(PleskServerFileManager::class);
        $downloader->setShellExecutor(PleskShellExecutor::class);

        $status = $downloader->download($downloadTargetPath);

        $this->assertTrue(is_dir($downloadTargetPath));
        $this->assertTrue(is_dir($downloadTargetPath.'/vendor'));
        $this->assertTrue(is_dir($downloadTargetPath.'/userfiles'));
        $this->assertTrue(is_file($downloadTargetPath.'/index.php'));
    }

}
