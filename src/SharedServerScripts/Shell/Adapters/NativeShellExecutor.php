<?php
namespace MicroweberPackages\SharedServerScripts\Shell\Adapters;

class NativeShellExecutor implements IShellExecutor
{
    /**
     * @param string $file
     * @param array $args
     * @return mixed|string
     */
    public function executeFile(string $file, array $args)
    {
        $processArgs = [];
        $processArgs[] = $file;
        $processArgs = array_merge($processArgs, $args);

        return $this->executeCommand($processArgs);
    }

    public function executeCommand(array $args, $cwd = null)
    {
        $args = implode(' ', array_map('escapeshellarg', $args));
        $exec = shell_exec($args);
        return $exec;
    }
}
