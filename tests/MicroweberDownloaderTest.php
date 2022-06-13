<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\Files\Adapters\DefaultFileAdapter;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\DefaultShellAdapter;
use PHPUnit\Framework\TestCase;

class MicroweberDownloaderTest extends TestCase
{
    public function testDownload()
    {
        $downloader = new MicroweberDownloader(DefaultFileAdapter::class,DefaultShellAdapter::class);
        $downloader->download(__DIR__.'/vai');
    }

}
