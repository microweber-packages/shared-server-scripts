<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\Interfaces\IMicroweberDownloader;
use MicroweberPackages\SharedServerScripts\Shell\ShellExecutor;

class MicroweberDownloader implements IMicroweberDownloader {

    public function download(string $target)
    {
        if (!is_dir($target)) {
            throw new Exception('Target path is not valid.');
        }
        if (!is_writable($target)) {
            throw new Exception('Target path is not writable.');
        }

      //  $shellExecutor = new ShellExecutor();
    //    $status = $shellExecutor->executeFile('unzip_app_version.sh', ['', $target]);

       // dd($status);
    }

    public function getRelease()
    {
        // TODO: Implement getRelease() method.
    }

}
