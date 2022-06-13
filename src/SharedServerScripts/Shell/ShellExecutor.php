<?php

namespace MicroweberPackages\SharedServerScripts\Shell;


class ShellExecutor
{
    public $adapter = DefaultShellAdapter::class;

    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    public function executeFile($file, $args)
    {
        return $this->adapter->executeFile($file, $args);
    }
}
