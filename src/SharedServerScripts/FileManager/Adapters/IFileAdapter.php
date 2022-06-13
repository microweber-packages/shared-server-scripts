<?php

namespace MicroweberPackages\SharedServerScripts\FileManager\Adapters;

interface IFileAdapter
{
    public function isDir(string $dir);
    public function isWritable(string $dir);

}
