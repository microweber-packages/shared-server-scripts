<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\Interfaces\IMicroweberDownloader;

class MicroweberDownloader implements IMicroweberDownloader {

    public function download(string $target)
    {
        if (!is_dir($target)) {
            throw new Exception('Target path is not valid.');
        }
        if (!is_writable($target)) {
            throw new Exception('Target path is not writable.');
        }

        dump($target);
    }

    public function getRelease()
    {
        // TODO: Implement getRelease() method.
    }

}
