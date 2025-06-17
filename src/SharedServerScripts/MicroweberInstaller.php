<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberInstaller
{
    use MicroweberFileOperationsTrait;

    public const TYPE_STANDALONE = 'standalone';
    public const TYPE_SYMLINK = 'symlink';

    public const DATABASE_DRIVER_MYSQL = 'mysql';
    public const DATABASE_DRIVER_SQLITE = 'sqlite';

    /**
     * @var string
     */
    protected $type = self::TYPE_STANDALONE;

    /**
     * @var string
     */
    protected $databaseDriver = self::DATABASE_DRIVER_SQLITE;

    /**
     * @var string
     */
    protected $databaseHost = 'localhost:3306';

    /**
     * @var
     */
    protected $databaseUsername;

    /**
     * @var
     */
    protected $databasePassword;

    /**
     * @var
     */
    protected $databaseName;

    /**
     * @var string
     */
    protected $adminEmail = 'admin@microweber.com';

    /**
     * @var string
     */
    protected $adminUsername = '';

    /**
     * @var string
     */
    protected $adminPassword = '';

    /**
     * @var bool
     */
    protected $chownUser = false;

    /**
     * @var bool
     */
    protected $chownAfterInstall = false;

    /**
     * @var bool
     */
    protected $logger = false;

    /**
     * @var string
     */
    protected $template = null;

    /**
     * @var string
     */
    protected $language = 'en';

    /**
     * @var string
     */
    protected $phpSbin = 'php';


    /**
     * @var string
     */
    protected $appUrl = '';

    /**
     * @param $logger
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function setSymlinkInstallation()
    {
        $this->type = self::TYPE_SYMLINK;
    }

    public function setStandaloneInstallation()
    {
        $this->type = self::TYPE_STANDALONE;
    }

    /**
     * @param $language
     * @return void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @param $template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param $driver
     * @return void
     */
    public function setDatabaseDriver($driver)
    {
        $this->databaseDriver = $driver;
    }

    /**
     * @param $username
     * @return void
     */
    public function setDatabaseUsername($username)
    {
        $this->databaseUsername = $username;
    }

    /**
     * @param $password
     * @return void
     */
    public function setDatabasePassword($password)
    {
        $this->databasePassword = $password;
    }

    /**
     * @param $host
     * @return void
     */
    public function setDatabaseHost($host)
    {
        $this->databaseHost = $host;
    }

    /**
     * @param $name
     * @return void
     */
    public function setDatabaseName($name)
    {
        $this->databaseName = $name;
    }

    /**
     * @param $email
     * @return void
     */
    public function setAdminEmail($email)
    {
        $this->adminEmail = $email;
    }

    /**
     * @param $username
     * @return void
     */
    public function setAdminUsername($username)
    {
        $this->adminUsername = $username;
    }

    /**
     * @param $password
     * @return void
     */
    public function setAdminPassword($password)
    {
        $this->adminPassword = $password;
    }

    /**
     * @param $user
     * @return void
     */
    public function setChownUser($user)
    {
        $this->chownUser = $user;
    }

    public function getChownUser()
    {
        if ($this->chownUser) {
            return $this->chownUser;
        }

        return $this->getFileOwnership($this->path);
    }

    /**
     * @return void
     */
    public function enableChownAfterInstall()
    {
        $this->chownAfterInstall = true;
    }

    /**
     * @param $phpSbin
     *
     * @return void
     */

    public function setPhpSbin($phpSbin)
    {
        $this->phpSbin = $phpSbin;
    }

    public function __construct()
    {
        $this->initializeAdapters();
    }

    public function run()
    {
        $this->ensureTargetDirectory();

        // Clear domain files if exists
        $this->backupExistingFiles();

        // First we will make directories
        $this->createDirectories($this->getDirsToMake());

        foreach ($this->getFilesForSymlinking() as $fileOrFolder) {
            $sourceDirOrFile = $this->sourcePath . '/' . $fileOrFolder;
            $targetDirOrFile = $this->path . '/' . $fileOrFolder;

            if (!$this->fileManager->fileExists($sourceDirOrFile)) {
                continue;
            }

            $this->processFileOrFolder($sourceDirOrFile, $targetDirOrFile, $this->type == self::TYPE_SYMLINK);
        }

        // Copy directories
        $this->copyDirectories($this->getDirsToCopy());

        // Copy files
        $this->copyFiles($this->getFilesForCopy());

        // Copy files with custom targets
        $this->copyFilesWithTargets($this->getFilesForCopyWithTarget());

        if ($this->type == self::TYPE_SYMLINK) {
            $this->fixHtaccessForSymlink();
        }

        if ($this->databaseDriver == self::DATABASE_DRIVER_SQLITE) {
            $this->databaseName = $this->path . '/storage/database.sqlite';
        }

        $installArguments = $this->buildInstallArguments();
        $artisanCommand = array_merge([
            $this->phpSbin,
            '-d memory_limit=512M',
            $this->path . '/artisan',
            'microweber:install',
        ], $installArguments);

        try {
            $this->_chownFolders();

            $executeArtisan = $this->shellExecutor->executeCommand($artisanCommand, $this->path, [
                'APP_ENV' => false,
                'DB_CONNECTION' => false,
                'APP_KEY' => false,
                'SYMFONY_DOTENV_VARS' => false,
            ]);

            $this->_chownFolders();

            $success = false;
            if (strpos($executeArtisan, 'done') !== false) {
                $success = true;
            }

            return ['success' => $success, 'log' => $executeArtisan];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => true, 'log' => $e->getMessage()];
        }
    }

    private function buildInstallArguments()
    {
        $installArguments = [];

        // Admin details
        $installArguments[] = '--email=' . $this->adminEmail;
        $installArguments[] = '--username=' . $this->adminUsername;
        $installArguments[] = '--password=' . $this->adminPassword;

        // Database settings
        if ($this->databaseDriver == self::DATABASE_DRIVER_SQLITE) {
            $installArguments[] = '--db-name=' . $this->databaseName;
        } else {
            $installArguments[] = '--db-host=' . $this->databaseHost;
            $installArguments[] = '--db-name=' . $this->databaseName;
        }
        $installArguments[] = '--db-username=' . $this->databaseUsername;
        $installArguments[] = '--db-password=' . $this->databasePassword;
        $installArguments[] = '--db-driver=' . $this->databaseDriver;

        if ($this->language) {
            $installArguments[] = '--language=' . trim($this->language);
        }


        if ($this->appUrl) {

            //add https
            if (strpos($this->appUrl, 'https://') === false && strpos($this->appUrl, 'http://') === false) {
                $this->appUrl = 'https://' . $this->appUrl;
            }

            //replace http with https if it is set
            if (strpos($this->appUrl, 'http://') === 0) {
                $this->appUrl = 'https://' . substr($this->appUrl, 7);
            }

            $installArguments[] = '--app-url=' . trim($this->appUrl);
        }

        $installArguments[] = '--db-prefix=site_';

        if (!empty($this->template)) {
            $installArguments[] = '--template=' . $this->template;
            $installArguments[] = '--default-content=1';
        }

        return $installArguments;
    }

    public function _chownFolders()
    {
        if ($this->chownAfterInstall) {
            $chownUser = $this->getChownUser();
            if ($chownUser) {
                $this->executeChownScript($chownUser);
            }
        }
    }

    public function getAppUrl(): string
    {
        return $this->appUrl;
    }

    public function setAppUrl(string $appUrl): void
    {
        $this->appUrl = $appUrl;
    }
}
