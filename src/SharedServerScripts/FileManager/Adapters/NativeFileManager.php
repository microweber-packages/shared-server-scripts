<?php
namespace MicroweberPackages\SharedServerScripts\FileManager\Adapters;

class NativeFileManager implements IFileManager
{

    /**
     * @param $dir
     * @return bool
     */
    public function isDir($dir)
    {
        return is_dir($dir);
    }

    /**
     * @param $dir
     * @return bool
     */
    public function isWritable($dir)
    {
        return is_writable($dir);
    }

    /**
     * @param $dir
     * @return bool
     */
    public function isFile($dir)
    {
        return is_file($dir);
    }

    /**
     * @param $path
     * @return array|string|string[]
     */
    public function fileExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public function fileExists($file)
    {
        return file_exists($file);
    }

    public function scanDir($dir)
    {
        return scandir($dir);
    }

    public function filemtime($file)
    {
        return filemtime($file);
    }

    public function fileGetContents($file)
    {
        return file_get_contents($file);
    }

}
