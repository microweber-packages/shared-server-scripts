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
     * @param int $permissions
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dir, $permissions = 0755, $recursive = true)
    {
        return mkdir($dir, $permissions, $recursive);
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
     * @return false|string
     */
    public function filePutContents($file, $content)
    {
        return file_put_contents($file, $content);
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

    /**
     * @param $from
     * @param $to
     * @return bool
     */
    public function copyFolder($from, $to)
    {

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                if (!is_dir($to . DIRECTORY_SEPARATOR . $iterator->getSubPathname())) {
                    // Create the directory if it does not exist
                    mkdir($to . DIRECTORY_SEPARATOR . $iterator->getSubPathname(), 0755, true);
                }


            } else {

                if(!is_dir(dirname($to . DIRECTORY_SEPARATOR . $iterator->getSubPathname()))){
                    // Create the parent directory if it does not exist
                    mkdir(dirname($to . DIRECTORY_SEPARATOR . $iterator->getSubPathname()), 0755, true);
                }

                if (is_file($item)  and !is_dir($item)) {

                    copy($item, $to . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
                }
            }
        }
    }


    /**
     * @param $target
     * @param $link
     * @return bool
     */
    public function symlink($target, $link)
    {
        try {
            $exec = symlink($target, $link);
        } catch (\Exception $e) {
            throw new \Exception(json_encode(['args' => func_get_args(), 'message' => $e->getMessage()], JSON_PRETTY_PRINT));
        }

        return $exec;
    }

    /**
     * @param $file
     * @return bool
     */
    public function unlink($file)
    {
        return unlink($file);
    }

    /**
     * @param $file
     * @return bool
     */
    public function rmdir($file)
    {
        return rmdir($file);
    }

    /**
     * @param $dir
     * @return bool
     */
    public function rmdirRecursive($dir)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {

            if ($fileinfo->isLink()) {
                unlink($fileinfo->getPathName()); // must be path name, cause will delete the source of symlink
            }

            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            }

            if ($fileinfo->isFile()) {
                unlink($fileinfo->getRealPath());
            }
        }

        if (is_dir($dir)) {
            rmdir($dir);
        }

    }
}
