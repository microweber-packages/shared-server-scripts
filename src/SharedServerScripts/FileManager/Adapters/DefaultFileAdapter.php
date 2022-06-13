<?php
namespace MicroweberPackages\SharedServerScripts\FileManager\Adapters;

class DefaultFileAdapter
{

    public function isDir($dir)
    {
        return is_dir($dir);
    }

    public function isWritable($dir)
    {
        return is_writable($dir);
    }

}
