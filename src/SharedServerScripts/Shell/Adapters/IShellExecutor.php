<?php

namespace MicroweberPackages\SharedServerScripts\Shell\Adapters;

interface IShellExecutor
{
    public function executeFile(string $file, array $args);
}
