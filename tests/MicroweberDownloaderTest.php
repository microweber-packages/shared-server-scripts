<?php

namespace MicroweberPackages\SharedServerScripts;

use PHPUnit\Framework\TestCase;
use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberDownloaderTest extends TestCase
{
    public function testDownload()
    {
        $downloader = new MicroweberDownloader(NativeFileManager::class,NativeShellExecutor::class);
        $status = $downloader->download(__DIR__.'/microweber-latest');

        $this->assertTrue(is_dir(__DIR__.'/microweber-latest'));
        $this->assertTrue(is_dir(__DIR__.'/microweber-latest/vendor'));
        $this->assertTrue(is_dir(__DIR__.'/microweber-latest/userfiles'));
        $this->assertTrue(is_file(__DIR__.'/microweber-latest/index.php'));
    }

}
