<?php

namespace MicroweberPackages\SharedServerScripts\Shell;

use MicroweberPackages\SharedServerScripts\Shell\Adapters\DefaultShellAdapter;

class ShellExecutor
{
    public $adapter;

    public function __construct()
    {
        $this->adapter = new DefaultShellAdapter();
    }

    public function setAdapter($adapter)
    {
        $this->adapter = new $adapter();
    }

    public function executeFile(string $file, array $args)
    {
        return $this->adapter->executeFile($file, $args);
    }
}
