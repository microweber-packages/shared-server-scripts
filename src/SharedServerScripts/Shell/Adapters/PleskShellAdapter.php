<?php
namespace MicroweberPackages\SharedServerScripts\Shell\Adapters;

class PleskShellAdapter implements IShellExecutor
{
    public function executeFile(string $file, array $args)
    {
        return pm_ApiCli::callSbin($file, $args);
    }
}
