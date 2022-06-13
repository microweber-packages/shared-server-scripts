<?php
namespace MicroweberPackages\SharedServerScripts\Interfaces;

interface IMicroweberDownloader {

    public function download(string $target);
    public function getRelease();

}
