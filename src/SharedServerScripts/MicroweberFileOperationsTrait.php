<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

/**
 * Trait MicroweberFileOperationsTrait
 *
 * Shared functionality for Microweber installer, reinstaller, and uninstaller classes.
 * Provides common file operations, path management, and adapter handling.
 */
trait MicroweberFileOperationsTrait
{
    /**
     * @var NativeFileManager
     */
    protected $fileManager;

    /**
     * @var NativeShellExecutor
     */
    protected $shellExecutor;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * Initialize the file manager and shell executor with default adapters
     */
    protected function initializeAdapters()
    {
        $this->fileManager = new NativeFileManager();
        $this->shellExecutor = new NativeShellExecutor();
    }

    /**
     * @param $adapter
     * @return void
     */
    public function setFileManager($adapter)
    {
        $this->fileManager = $adapter;
    }

    /**
     * @param $adapter
     * @return void
     */
    public function setShellExecutor($adapter)
    {
        $this->shellExecutor = $adapter;
    }

    /**
     * @param $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param $path
     * @return void
     */
    public function setSourcePath($path)
    {
        $this->sourcePath = $path;
    }

    /**
     * Check if the source directory contains Microweber v3 structure
     *
     * @return bool
     */
    protected function isMicroweberV3()
    {
        if (!$this->sourcePath) {
            return false;
        }

        return $this->fileManager->isDir($this->sourcePath . '/Templates');
    }

    /**
     * Get directories that need to be created during installation
     *
     * @return array
     */
    protected function getDirsToMake()
    {
        $dirs = [];

        // Storage dirs
        $dirs[] = 'storage';
        $dirs[] = 'storage/framework';
        $dirs[] = 'storage/framework/sessions';
        $dirs[] = 'storage/framework/views';
        $dirs[] = 'storage/cache';
        $dirs[] = 'storage/logs';
        $dirs[] = 'storage/app';
        $dirs[] = 'database';

        // Bootstrap dirs
        $dirs[] = 'bootstrap';
        $dirs[] = 'bootstrap/cache';

        if ($this->isMicroweberV3()) {
            $dirs[] = 'Modules';
            $dirs[] = 'Templates';
        } else {
            // User files dirs
            $dirs[] = 'userfiles';
            $dirs[] = 'userfiles/media';
            $dirs[] = 'userfiles/modules';
            $dirs[] = 'userfiles/templates';
        }

        // Public
        $dirs[] = 'public';

        return $dirs;
    }

    /**
     * Get directories that need to be copied during installation
     *
     * @return array
     */
    protected function getDirsToCopy()
    {
        $dirs = [];

        // Config dir
        $dirs[] = 'config';
        $dirs[] = 'public/vendor';
        $dirs[] = 'public/build';
        $dirs[] = 'public/modules';
        $dirs[] = 'public/templates';
        $dirs[] = 'public/js';
        $dirs[] = 'public/css';

        return $dirs;
    }

    /**
     * Get files that should be symlinked or copied for core functionality
     *
     * @return array
     */
    protected function getFilesForSymlinking()
    {
        $files = [];
        $files[] = 'vendor';
        $files[] = 'src';
        $files[] = 'resources';
        $files[] = 'database/migrations';
        $files[] = 'database/seeds';
        $files[] = 'database/factories';
        $files[] = 'public/build';
        $files[] = 'storage/branding_saas.json';
        $files[] = 'version.txt';

        if (!$this->isMicroweberV3()) {
            $files[] = 'userfiles/elements';

            // Microweber v2 paths
            $this->addMicroweberV2Files($files, 'userfiles/templates');
            $this->addMicroweberV2Files($files, 'userfiles/modules');
        } else {
            // Microweber v3 paths
            $files[] = 'routes';
            $files[] = 'app';
            $files[] = 'packages';

            $this->addMicroweberV3Files($files, 'Templates');
            $this->addMicroweberV3Files($files, 'Modules');
        }

        return $files;
    }

    /**
     * Add Microweber v2 files to the files array
     *
     * @param array &$files
     * @param string $basePath
     */
    protected function addMicroweberV2Files(&$files, $basePath)
    {
        if (!$this->sourcePath || !$this->fileManager->isDir($this->sourcePath . '/' . $basePath)) {
            return;
        }

        $items = $this->fileManager->scanDir($this->sourcePath . '/' . $basePath);
        if (!empty($items)) {
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                $files[] = $basePath . '/' . $item;
            }
        }
    }

    /**
     * Add Microweber v3 files to the files array
     *
     * @param array &$files
     * @param string $basePath
     */
    protected function addMicroweberV3Files(&$files, $basePath)
    {
        if (!$this->sourcePath || !$this->fileManager->isDir($this->sourcePath . '/' . $basePath)) {
            return;
        }

        $items = $this->fileManager->scanDir($this->sourcePath . '/' . $basePath);
        if (!empty($items)) {
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                $files[] = $basePath . '/' . $item;
            }
        }
    }

    /**
     * Get files that need to be copied (not symlinked)
     *
     * @return array
     */
    protected function getFilesForCopy()
    {
        $files = [];

        // Index
        $files[] = 'phpunit.xml';

        if ($this->isMicroweberV3()) {
            $files[] = 'public/index.php';
            $files[] = 'public/favicon.ico';
            $files[] = 'public/.htaccess';
        } else {
            $files[] = 'index.php';
        }

        $files[] = '.htaccess';
        $files[] = 'favicon.ico';
        $files[] = 'composer.json';
        $files[] = 'artisan';

        // Bootstrap folder
        $files[] = 'bootstrap/.htaccess';
        $files[] = 'bootstrap/app.php';
        $files[] = 'bootstrap/autoload.php';

        return $files;
    }

    /**
     * Get files that need to be copied with specific target names
     *
     * @return array
     */
    protected function getFilesForCopyWithTarget()
    {
        $files = [];
        $files['.env.testing'] = '.env';
        return $files;
    }

    /**
     * Create or copy a file/folder from source to target
     *
     * @param string $sourceItem
     * @param string $targetItem
     * @param bool $isSymlink
     */
    protected function processFileOrFolder($sourceItem, $targetItem, $isSymlink = false)
    {
        if (!$this->fileManager->fileExists($sourceItem)) {
            return;
        }

        if ($isSymlink) {
            $this->fileManager->symlink($sourceItem, $targetItem);
        } else {
            if ($this->fileManager->isDir($sourceItem)) {
                $this->fileManager->copyFolder($sourceItem, $targetItem);
            } else if ($this->fileManager->isFile($sourceItem)) {
                $this->fileManager->copy($sourceItem, $targetItem);
            }
        }
    }

    /**
     * Remove a file or directory (including symlinks)
     *
     * @param string $target
     */
    protected function removeFileOrFolder($target)
    {
        if ($this->fileManager->isLink($target)) {
            $this->fileManager->unlink($target);
        } else if ($this->fileManager->isDir($target)) {
            $this->fileManager->rmdirRecursive($target);        } else if ($this->fileManager->isFile($target)) {
            $this->fileManager->unlink($target);
        }
    }

    /**
     * Create directories with proper permissions
     *
     * @param array $directories
     */
    protected function createDirectories($directories)
    {
        foreach ($directories as $dir) {
            $fullPath = $this->path . '/' . $dir;
            if (!$this->fileManager->isDir($fullPath)) {
                $this->fileManager->mkdir($fullPath, 0755, true);
            }
        }
    }

    /**
     * Copy directories from source to target
     *
     * @param array $directories
     */
    protected function copyDirectories($directories)
    {
        foreach ($directories as $folder) {
            $sourceDir = $this->sourcePath . '/' . $folder;
            $targetDir = $this->path . '/' . $folder;

            if (!$this->fileManager->isDir($sourceDir)) {
                continue;
            }

            $this->fileManager->copyFolder($sourceDir, $targetDir);
        }
    }

    /**
     * Copy files from source to target
     *
     * @param array $files
     */
    protected function copyFiles($files)
    {
        foreach ($files as $file) {
            $sourceFile = $this->sourcePath . '/' . $file;
            $targetFile = $this->path . '/' . $file;

            if (!$this->fileManager->isFile($sourceFile)) {
                continue;
            }

            $this->fileManager->copy($sourceFile, $targetFile);
        }
    }

    /**
     * Copy files with custom target names
     *
     * @param array $filesWithTargets
     */
    protected function copyFilesWithTargets($filesWithTargets)
    {
        foreach ($filesWithTargets as $sourceFile => $targetFile) {
            $sourceFilePath = $this->sourcePath . '/' . $sourceFile;
            $targetFilePath = $this->path . '/' . $targetFile;

            if (!$this->fileManager->isFile($sourceFilePath)) {
                continue;
            }            $this->fileManager->copy($sourceFilePath, $targetFilePath);
        }
    }

    /**
     * Backup existing files in the target directory
     */
    protected function backupExistingFiles()
    {
        try {
            $existingFiles = [];
            $scanResult = $this->fileManager->scanDir($this->path);

            if ($scanResult) {
                foreach ($scanResult as $file) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    $existingFiles[] = $file;
                }
            }

            if (!empty($existingFiles)) {
                // Make backup dir
                $backupMainFilesPath = $this->path . '/backup_files/';
                if (!$this->fileManager->isDir($backupMainFilesPath)) {
                    $this->fileManager->mkdir($backupMainFilesPath, 0755, true);
                }

                $backupFilesPath = $backupMainFilesPath . 'backup-' . date('Y-m-d-H-i-s');
                if (!$this->fileManager->isDir($backupFilesPath)) {
                    $this->fileManager->mkdir($backupFilesPath, 0755, true);
                }

                // Move files to backup dir
                foreach ($existingFiles as $file) {
                    if ($file == 'backup_files') {
                        continue;
                    }
                    $this->fileManager->moveFile($this->path . '/' . $file, $backupFilesPath . '/' . $file);
                }
            }
        } catch (\Exception $e) {
            // Handle backup errors silently
        }
    }

    /**
     * Validate that source and target paths exist and are writable
     *
     * @throws \Exception
     */
    protected function validatePaths()
    {
        if (!$this->path) {
            throw new \Exception('Target path is not set');
        }

        if (!$this->fileManager->isDir(dirname($this->path))) {
            throw new \Exception('Parent directory of target path does not exist: ' . dirname($this->path));
        }        if (!$this->fileManager->isWritable(dirname($this->path))) {
            throw new \Exception('Parent directory of target path is not writable: ' . dirname($this->path));
        }
    }

    /**
     * Ensure target directory exists
     */
    protected function ensureTargetDirectory()
    {
        if (!$this->fileManager->isDir($this->path)) {
            $this->fileManager->mkdir($this->path, 0755, true);
        }
    }

    /**
     * Check if source exists before processing
     *
     * @param string $sourcePath
     * @return bool
     */
    protected function sourceExists($sourcePath)
    {
        return $this->fileManager->isDir($sourcePath) || $this->fileManager->isFile($sourcePath);
    }

    /**
     * Generate a random password
     *
     * @param int $length
     * @param bool $complex
     * @return string
     */
    public static function getRandomPassword($length = 16, $complex = false)
    {
        $alphabet = 'ghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        if ($complex) {
            $alphabet_complex = '!@#$%^&*?_~';
        }

        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        if ($complex) {
            $alphaLength = strlen($alphabet_complex) - 1;
            for ($i = 0; $i < $length; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet_complex[$n];
            }

            shuffle($pass);
        }        return implode($pass);
    }

    /**
     * Execute file ownership change script
     *
     * @param string $chownUser
     * @return string|null
     */
    protected function executeChownScript($chownUser)
    {
        if (!$chownUser || !$this->path) {
            return null;
        }

        try {
            $scriptPath = $this->getShellScriptPath('chown_installed_app.sh');
            return $this->shellExecutor->executeFile($scriptPath, [$chownUser, $this->path]);
        } catch (\Exception $e) {
            // Handle chown errors silently
            return null;
        }
    }

    /**
     * Get the path to a shell script
     *
     * @param string $scriptName
     * @return string
     */
    protected function getShellScriptPath($scriptName)
    {
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'shell-scripts' . DIRECTORY_SEPARATOR . $scriptName;
    }

    /**
     * Get file ownership information
     *
     * @param string $file
     * @return string|null
     */
    protected function getFileOwnership($file)
    {
        if (!$this->fileManager->fileExists($file)) {
            return null;
        }

        $stat = stat($file);
        if ($stat && function_exists('posix_getpwuid')) {
            $user = posix_getpwuid($stat[4]);
            if (isset($user['name'])) {
                return $user['name'];
            }
        }

        return null;
    }

    /**
     * Fix htaccess file for symlink installations
     *
     * @return bool
     */
    protected function fixHtaccessForSymlink()
    {
        try {
            $htaccessPath = $this->path . '/.htaccess';
            if (!$this->fileManager->fileExists($htaccessPath)) {
                return false;
            }

            $content = $this->fileManager->fileGetContents($htaccessPath);
            $content = str_replace('-MultiViews -Indexes', 'FollowSymLinks', $content);

            return $this->fileManager->filePutContents($htaccessPath, $content) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Scan directory and filter out system entries
     *
     * @param string $dirPath
     * @param string $extension Filter by file extension (optional)
     * @return array
     */
    protected function scanDirectoryFiltered($dirPath, $extension = null)
    {
        if (!$this->fileManager->isDir($dirPath)) {
            return [];
        }

        $items = $this->fileManager->scanDir($dirPath);
        if (!$items) {
            return [];
        }

        $filtered = [];
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if ($extension && $this->fileManager->fileExtension($dirPath . '/' . $item) !== $extension) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * Add missing configuration files from source to target
     *
     * @param string $configType Type of config (e.g., 'config', 'storage')
     * @param string $extension File extension to filter by
     */
    protected function addMissingConfigFiles($configType = 'config', $extension = 'php')
    {
        $sourceConfigPath = $this->sourcePath . '/' . $configType;
        $targetConfigPath = $this->path . '/' . $configType;

        $sourceConfigs = $this->scanDirectoryFiltered($sourceConfigPath, $extension);
        $targetConfigs = $this->scanDirectoryFiltered($targetConfigPath, $extension);

        $missingConfigs = array_diff($sourceConfigs, $targetConfigs);

        foreach ($missingConfigs as $configFile) {
            $sourceFile = $sourceConfigPath . '/' . $configFile;
            $targetFile = $targetConfigPath . '/' . $configFile;

            if (!$this->fileManager->fileExists($targetFile)) {
                $this->fileManager->copy($sourceFile, $targetFile);
            }
        }
    }

    /**
     * Log operation result
     *
     * @param string $operation
     * @param bool $success
     * @param string $message
     */
    protected function logOperation($operation, $success, $message = '')
    {
        if ($this->logger) {
            $logData = [
                'operation' => $operation,
                'success' => $success,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Assuming logger has a log method
            if (method_exists($this->logger, 'log')) {
                $this->logger->log($success ? 'info' : 'error', json_encode($logData));
            }
        }
    }

    /**
     * Build operation result array
     *
     * @param bool $success
     * @param string $message
     * @param array $data Additional data
     * @return array
     */
    protected function buildResult($success, $message = '', $data = [])
    {
        $result = [
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return array_merge($result, $data);
    }


    public function getFileManager()
    {
        if(!$this->fileManager) {
            $this->initializeAdapters();
        }

        return $this->fileManager;

    }
}
