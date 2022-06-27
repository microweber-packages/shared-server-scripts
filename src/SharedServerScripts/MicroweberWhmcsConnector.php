<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberWhmcsConnector
{
    /**
     * @var
     */
    public $domain;

    /**
     * @var
     */
    public $path;

    /**
     * @var
     */
    public $url;

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
     * @param $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    public function __construct() {
        $this->fileManager = new NativeFileManager();
        $this->shellExecutor = new NativeShellExecutor();
    }

    public function applySettingsToPath()
    {
        $whmcsJson = [];
        $whmcsJson['url'] = $this->url;
        $whmcsJson['whmcs_url'] = $this->url;

        $whmcsJson = json_encode($whmcsJson, JSON_PRETTY_PRINT);

        $whmFilePath = $this->path . 'userfiles/modules/whmcs-connector/';
        $whmFileName = 'settings.json';

        if (!$this->fileManager->isFile($whmFilePath)) {
            $this->fileManager->mkdir($whmFilePath, null, true);
        }

        $this->fileManager->filePutContents($whmFilePath . $whmFileName, $whmcsJson);

    }

    public function getSelectedTemplateFromWhmcsUser()
    {
        $template = 'new-world';
        $url = $this->url . '/index.php?m=microweber_addon&function=get_domain_template_config&domain=' . $this->domainName;
        $json = $this->fileManager->fileGetContents($url);

        if (isset($json['template'])) {
            $template = $json['template'];
        }

        return $template;
    }

    public function getWhitelabelSettings()
    {
        $settings = array();

        $url = $this->url . '/index.php?m=microweber_server&function=get_whitelabel_settings&domain=' . $this->domainName;
        $json = $this->fileManager->fileGetContents($url);

        if (isset($json['settings'])) {
            $settings = $json['settings'];
        }

        return $settings;
    }

}