<?php
namespace MicroweberPackages\SharedServerScripts\Shell\Adapters;

use Symfony\Component\Process\Process;

class DefaultShellAdapter implements IShellExecutor
{
    public function executeFile(string $file, array $args)
    {
        $processArgs = [];
        $processArgs[] = $file;
        $processArgs = array_merge($processArgs, $args);

        $process = new Process($processArgs);
        $process->run();
        $output = $process->getOutput();

        dd($output);
    }
}
