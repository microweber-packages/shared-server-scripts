<?php

namespace MicroweberPackages\SharedServerScripts;

use PHPUnit\Framework\TestCase;
use MicroweberPackages\SharedServerScripts\FileManager\Adapters\DefaultFileAdapter;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\DefaultShellAdapter;

class MicroweberDownloaderTest extends TestCase
{
    public function testDownload()
    {
        $downloader = new MicroweberDownloader(DefaultFileAdapter::class,DefaultShellAdapter::class);
        $downloader->download(__DIR__.'/vai');
    }

}
