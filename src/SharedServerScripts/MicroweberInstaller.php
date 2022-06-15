<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberInstaller {

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
    protected $path = false;

    /**
     * @var bool
     */
    protected $sourcePath = false;


    /**
     * @var bool
     */
    protected $logger = false;

    /**
     * @var string
     */
    protected $template = 'new-world';

    /**
     * @var string
     */
    protected $language = 'en';

    /**
     * @param $logger
     * @return void
     */
    public function setLogger($logger) {
        $this->logger = $logger;
    }

    /**
     * @param $path
     * @return void
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @param $path
     * @return void
     */
    public function setSourcePath($path) {
        $this->sourcePath = $path;
    }

    /**
     * @param $type
     * @return void
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @param $language
     * @return void
     */
    public function setLanguage($language) {
        $this->language = $language;
    }

    /**
     * @param $template
     * @return void
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * @param $driver
     * @return void
     */
    public function setDatabaseDriver($driver) {
        $this->databaseDriver = $driver;
    }

    /**
     * @param $email
     * @return void
     */
    public function setAdminEmail($email) {
        $this->adminEmail = $email;
    }

    /**
     * @param $username
     * @return void
     */
    public function setAdminUsername($username) {
        $this->adminUsername = $username;
    }

    /**
     * @param $password
     * @return void
     */
    public function setAdminPassword($password) {
        $this->adminPassword = $password;
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

    public function __construct() {
        $this->fileManager = new NativeFileManager();
        $this->shellExecutor = new NativeShellExecutor();
    }

    public function run() {

        if (!$this->fileManager->isDir($this->path)) {
            $this->fileManager->mkdir($this->path);
        }

        $dbName =  str_replace('.', '', 'yourdomain');
        $dbName = substr($dbName, 0, 9);
        $dbName .= '_'.date('His');
        $dbUsername = $dbName;
        $dbPassword = $this->getRandomPassword(12, true);


        // Clear domain files if exists
        $this->_prepairPathFolder();

        // First we will make a directories
        foreach ($this->_getDirsToMake() as $dir) {
            $this->fileManager->mkdir($this->path . '/' . $dir, '0755', true);
        }

        $this->type = self::TYPE_SYMLINK;

        if ($this->type == self::TYPE_SYMLINK) {
            foreach ($this->_getFilesForSymlinking() as $folder) {

                $sourceDirOrFile = $this->sourcePath . '/' . $folder;
                $targetDirOrFile = $this->path . '/' . $folder;

                // Delete file
                if ($this->fileManager->isFile($targetDirOrFile)) {
                    $this->fileManager->unlink($targetDirOrFile);
                }

                // Create symlink
                $this->fileManager->symlink($sourceDirOrFile, $targetDirOrFile);

            }
        }

        // And then we will copy folders
        foreach ($this->_getDirsToCopy() as $folder) {
            $sourceDir = $this->sourcePath .'/'. $folder;
            $targetDir = $this->path .'/'. $folder;
            $this->fileManager->copyFolder($sourceDir, $targetDir);
        }

        // And then we will copy files
        foreach ($this->_getFilesForCopy() as $file) {
            $sourceFile = $this->sourcePath .'/'. $file;
            $targetFile = $this->path .'/'. $file;
            $this->fileManager->copy($sourceFile, $targetFile);
        }

        if ($this->type == self::TYPE_SYMLINK) {
            $this->_fixHtaccess();
        }


        echo 1; 
        die();

        $this->setProgress(85);

        $adminEmail = 'admin@microweber.com';
        $adminPassword = '1';
        $adminUsername = '1';

        if (!empty($this->_email)) {
            $adminEmail = $this->_email;
        }
        if (!empty($this->_password)) {
            $adminPassword = $this->_password;
        }
        if (!empty($this->_username)) {
            $adminUsername = $this->_username;
        }

        if ($this->_databaseDriver == 'mysql') {

            $dbHost = 'localhost:3306';
            if (isset($databaseServerDetails['host']) && isset($databaseServerDetails['port'])) {
                $dbHost = $databaseServerDetails['host'] . ':' . $databaseServerDetails['port'];
            }

        } else {
            $dbHost = 'localhost';
            $dbName = $domainDocumentRoot . '/storage/database1.sqlite';
        }

        $this->setProgress(90);

        $installArguments = [];

        // Admin details
        $installArguments[] =  $adminEmail;
        $installArguments[] =  $adminUsername;
        $installArguments[] =  $adminPassword;

        // Database settings
        $installArguments[] = $dbHost;
        $installArguments[] = $dbName;
        $installArguments[] = $dbUsername;
        $installArguments[] = $dbPassword;
        $installArguments[] = $this->_databaseDriver;

        if ($this->_language) {
            $installationLanguage = $this->_language;
        } else {
            $installationLanguage = pm_Settings::get('installation_language');
        }

        if (!empty($installationLanguage)) {
            $installArguments[] = '-l';
            $installArguments[] = trim($installationLanguage);
        }

        $installArguments[] = '-p';
        $installArguments[] = 'site_';

        if ($this->_template) {
            $installArguments[] = '-t';
            $installArguments[] = $this->_template;
        }

        $installArguments[] = '-d';
        $installArguments[] = '1';

        if (!$this->_template) {
            $installArguments[] = '-c';
            $installArguments[] = '1';
        }

        try {
            $args = [
                $domain->getSysUserLogin(),
                'exec',
                $domainDocumentRoot,
                $phpHandler['clipath'],
                'artisan',
                'microweber:install',
            ];
            $args = array_merge($args, $installArguments);
            $artisan = pm_ApiCli::callSbin('filemng', $args, pm_ApiCli::RESULT_FULL);



            return ['success'=>true, 'log'=> $artisan['stdout']];
        } catch (Exception $e) {
            return ['success'=>false, 'error'=>true, 'log'=> $e->getMessage()];
        }

    }

    private function checkSsl($domainName)
    {
        $g = @stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
        $r = @stream_socket_client("ssl://www.".$domainName.":443", $errno, $errstr, 30,
            STREAM_CLIENT_CONNECT, $g);
        $cont = @stream_context_get_params($r);
        if (isset($cont["options"]["ssl"]["peer_certificate"])) {
            return true;
        }

        return false;
    }

    private function addDomainEncryption($domain)
    {
        $artisan = false;

        $sslEmail = 'admin@microweber.com';

        $encryptOptions = [];
        $encryptOptions[] = '--domain';
        $encryptOptions[] = $domain->getName();
        $encryptOptions[] = '--email';
        $encryptOptions[] = $sslEmail;

        // Add SSL
        try {
            Modules_Microweber_Log::debug('Start installign SSL for domain: ' . $domain->getName() . '; SSL Email: ' . $sslEmail);

            $artisan = \pm_ApiCli::call('extension', array_merge(['--exec', 'letsencrypt', 'cli.php'], $encryptOptions), \pm_ApiCli::RESULT_FULL);

            Modules_Microweber_Log::debug('Encrypt domain log for: ' . $domain->getName() . '<br />' . $artisan['stdout']. '<br /><br />');
            Modules_Microweber_Log::debug('Success instalation SSL for domain: ' . $domain->getName());

        } catch(\Exception $e) {

            Modules_Microweber_Log::debug('Can\'t install SSL for domain: ' . $domain->getName());
            Modules_Microweber_Log::debug('Error: ' . $e->getMessage());

        }

        return $artisan;
    }

    private function _fixHtaccess()
    {
        try {

            $content = $this->fileManager->fileGetContents($this->path . '/.htaccess');
            $content = str_replace('-MultiViews -Indexes', 'FollowSymLinks', $content);

            $this->fileManager->filePutContents($this->path . '/.htaccess', $content);

        } catch (Exception $e) {
            // Error
        }
    }

    private function _prepairPathFolder()
    {
        try {
            $findedFiles = [];
            foreach ($this->fileManager->scanDir($this->path) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $findedFiles[] = $file;
            }

            if (!empty($findedFiles)) {

                // Make backup dir
                $backupMainFilesPath = $this->path . '/backup_files/';
                if (!$this->fileManager->isDir($backupMainFilesPath)) {
                    $this->fileManager->mkdir($backupMainFilesPath);
                }
                $backupFilesPath = $backupMainFilesPath . 'backup-' . date('Y-m-d-H-i-s');
                if (!$this->fileManager->isDir($backupFilesPath)) {
                    $this->fileManager->mkdir($backupFilesPath);
                }

                // Move files to backup dir
                foreach ($findedFiles as $file) {
                    if ($file == 'backup_files') {
                        continue;
                    }
                    $this->fileManager->moveFile($this->path . '/' . $file, $backupFilesPath . '/' . $file);
                }

            }

        } catch (Exception $e) {
            // error
        }

    }

    private function _getDirsToMake() {

        $dirs = [];

        // Storage dirs
        $dirs[] = 'storage';
        $dirs[] = 'storage/framework';
        $dirs[] = 'storage/framework/sessions';
        $dirs[] = 'storage/framework/views';
        $dirs[] = 'storage/cache';
        $dirs[] = 'storage/logs';
        $dirs[] = 'storage/app';

        // Bootstrap dirs
        $dirs[] = 'bootstrap';
        $dirs[] = 'bootstrap/cache';

        // User files dirs
        $dirs[] = 'userfiles';
        $dirs[] = 'userfiles/media';
        $dirs[] = 'userfiles/modules';
        $dirs[] = 'userfiles/templates';

        return $dirs;
    }

    private function _getDirsToCopy() {

        $dirs = [];

        // Config dir
        $dirs[] = 'config';

        return $dirs;
    }

    private function _getFilesForSymlinking() {

        $files = [];
        $files[] = 'version.txt';
        $files[] = 'vendor';
        $files[] = 'src';
        $files[] = 'resources';
        $files[] = 'database';
        $files[] = 'userfiles/elements';


        $listTemplates = $this->fileManager->scanDir($this->sourcePath . '/userfiles/templates');
        if (!empty($listTemplates)) {
            foreach ($listTemplates as $template) {
                if ($template == '.' || $template == '..') {
                    continue;
                }
                $files[] = '/userfiles/templates/' . $template;
            }
        }

        $listModules = $this->fileManager->scanDir($this->sourcePath . '/userfiles/modules');
        if (!empty($listModules)) {
            foreach ($listModules as $module) {
                if ($module == '.' || $module == '..') {
                    continue;
                }
                $files[] = '/userfiles/modules/' . $module;
            }
        }

        return $files;
    }

    /**
     * This is the files when symlinking app.
     * @return string[]
     */
    private function _getFilesForCopy() {

        $files = [];

        // Index
        $files[] = 'index.php';
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

    private function _generateIniFile($array, $i = 0) {
        $str = "";
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $str .= str_repeat(" ",$i*2)."[$k]".PHP_EOL;
                $str .= $this->_generateIniFile($v, $i+1);
            } else {
                $str .= str_repeat(" ", $i * 2) . "$k = $v" . PHP_EOL;
            }
        }
        return $str;
    }


    public static function getRandomPassword($length = 16, $complex = false)
    {
        $alphabet = 'ghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        if ($complex) {
            $alphabet_complex = '!@#$%^&*?_~';
        }

        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i ++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        if ($complex) {
            $alphaLength = strlen($alphabet_complex) - 1;
            for ($i = 0; $i < $length; $i ++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet_complex[$n];
            }

            shuffle($pass);
        }

        return implode($pass);
    }

}
