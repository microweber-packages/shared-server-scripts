<?php

namespace MicroweberPackages\SharedServerScripts;

use PHPUnit\Framework\TestCase;

class MicroweberDownloaderTest extends TestCase
{
    public function testDownload()
    {

        $downloader = new MicroweberDownloader();
        $downloader->download(__DIR__.'/vai');

    }

}
