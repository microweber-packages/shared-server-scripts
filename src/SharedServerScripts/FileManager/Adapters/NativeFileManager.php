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
    public function mkdir($dir)
    {
        return mkdir($dir);
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

    /**
     * @param $file
     * @return bool
     */
    public function fileExists($file)
    {
        return file_exists($file);
    }

    /**
     * @param $dir
     * @return array|false
     */
    public function scanDir($dir)
    {
        return scandir($dir);
    }

    /**
     * @param $file
     * @return false|int
     */
    public function filemtime($file)
    {
        return filemtime($file);
    }

    /**
     * @param $file
     * @return false|int
     */
    public function filectime($file)
    {
        return filectime($file);
    }

    /**
     * @param $file
     * @return false|string
     */
    public function fileGetContents($file)
    {
        return file_get_contents($file);
    }

    /**
     * @param $file
     * @return bool
     */
    public function isLink($file)
    {
        return is_link($file);
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     */
    public function moveFile($from, $to)
    {
        return rename($from, $to);
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     */
    public function copy($from, $to)
    {
        return copy($from, $to);
    }

}
