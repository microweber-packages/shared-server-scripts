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
        $downloader->download(__DIR__.'/vai');
    }

}
