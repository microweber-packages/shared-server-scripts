<?php

namespace MicroweberPackages\SharedServerScripts;

use MicroweberPackages\SharedServerScripts\FileManager\Adapters\NativeFileManager;
use MicroweberPackages\SharedServerScripts\Shell\Adapters\NativeShellExecutor;

class MicroweberEnvFileWebsiteApply
{
    use MicroweberFileOperationsTrait;

    /**
     * Web path of Website
     * @var string
     */
    public $webPath;

    /**
     * Shared path of Microweber
     * @var string
     */
    public $sharedPath;

    /**
     * Environment file path
     * @var string
     */
    public $envFilePath;

    /**
     * Start separator for MW environment variables
     */
    const MW_ENV_START_SEPARATOR = '### MW_ENV_VARS_APPLY ###';

    /**
     * End separator for MW environment variables
     */
    const MW_ENV_END_SEPARATOR = '### END_MW_ENV_VARS_APPLY ###';

    /**
     * @param string $path
     * @return void
     */
    public function setWebPath($path)
    {
        $this->webPath = $path;
        $this->envFilePath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * @param string $path
     * @return void
     */
    public function setSharedPath($path)
    {
        $this->sharedPath = $path;
    }

    /**
     * Set custom environment file path
     * @param string $path
     * @return void
     */
    public function setEnvFilePath($path)
    {
        $this->envFilePath = $path;
    }

    /**
     * Apply environment variables to the .env file
     * @param array $envVars Associative array of environment variables
     * @return bool
     */
    public function applyEnvVars($envVars)
    {
        if (empty($this->envFilePath)) {
            throw new \Exception('Environment file path is not set. Use setWebPath() or setEnvFilePath() first.');
        }

        $fileManager = $this->getFileManager();

        // Read existing .env file content or create empty content
        $existingContent = '';
        if ($fileManager->fileExists($this->envFilePath)) {
            $existingContent = $fileManager->fileGetContents($this->envFilePath);
        }

        // Generate the MW environment variables block
        $mwEnvBlock = $this->generateMwEnvBlock($envVars);

        // Update or add the MW environment variables section
        $newContent = $this->updateEnvContent($existingContent, $mwEnvBlock);

        // Write the updated content back to the file
        return $fileManager->filePutContents($this->envFilePath, $newContent);
    }

    /**
     * Generate the MW environment variables block
     * @param array $envVars
     * @return string
     */
    private function generateMwEnvBlock($envVars)
    {
        $block = self::MW_ENV_START_SEPARATOR . PHP_EOL;

        foreach ($envVars as $key => $value) {
            // Escape quotes in values
            $escapedValue = $this->escapeEnvValue($value);
            $block .= $key . '=' . $escapedValue . PHP_EOL;
        }

        $block .= self::MW_ENV_END_SEPARATOR;

        return $block;
    }

    /**
     * Escape environment variable values
     * @param string $value
     * @return string
     */
    private function escapeEnvValue($value)
    {
        // If value contains spaces, quotes, or special characters, wrap in quotes
        if (preg_match('/[\s"\'#]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    /**
     * Update environment file content with MW variables block
     * @param string $existingContent
     * @param string $mwEnvBlock
     * @return string
     */
    private function updateEnvContent($existingContent, $mwEnvBlock)
    {
        $startPos = strpos($existingContent, self::MW_ENV_START_SEPARATOR);
        $endPos = strpos($existingContent, self::MW_ENV_END_SEPARATOR);

        if ($startPos !== false && $endPos !== false) {
            // Separators found - replace existing block
            $beforeBlock = substr($existingContent, 0, $startPos);
            $afterBlock = substr($existingContent, $endPos + strlen(self::MW_ENV_END_SEPARATOR));

            // Remove trailing newlines from before block and leading newlines from after block
            $beforeBlock = rtrim($beforeBlock);
            $afterBlock = ltrim($afterBlock, "\r\n");

            $newContent = $beforeBlock;
            if (!empty($beforeBlock)) {
                $newContent .= PHP_EOL . PHP_EOL;
            }
            $newContent .= $mwEnvBlock;
            if (!empty($afterBlock)) {
                $newContent .= PHP_EOL . PHP_EOL . $afterBlock;
            }

            return $newContent;
        } else {
            // Separators not found - add new block
            $newContent = $existingContent;

            // Add spacing if file already has content
            if (!empty(trim($existingContent))) {
                $newContent = rtrim($newContent) . PHP_EOL . PHP_EOL;
            }

            $newContent .= $mwEnvBlock;

            return $newContent;
        }
    }

    /**
     * Remove MW environment variables block from .env file
     * @return bool
     */
    public function removeMwEnvVars()
    {
        if (empty($this->envFilePath)) {
            throw new \Exception('Environment file path is not set. Use setWebPath() or setEnvFilePath() first.');
        }

        $fileManager = $this->getFileManager();

        if (!$fileManager->fileExists($this->envFilePath)) {
            return true; // Nothing to remove
        }


        $existingContent = $fileManager->fileGetContents($this->envFilePath);
        $startPos = strpos($existingContent, self::MW_ENV_START_SEPARATOR);
        $endPos = strpos($existingContent, self::MW_ENV_END_SEPARATOR);

        if ($startPos !== false && $endPos !== false) {
            // Remove the MW block
            $beforeBlock = substr($existingContent, 0, $startPos);
            $afterBlock = substr($existingContent, $endPos + strlen(self::MW_ENV_END_SEPARATOR));

            // Clean up extra newlines
            $beforeBlock = rtrim($beforeBlock);
            $afterBlock = ltrim($afterBlock, "\r\n");

            $newContent = $beforeBlock;
            if (!empty($beforeBlock) && !empty($afterBlock)) {
                $newContent .= PHP_EOL . PHP_EOL;
            }
            $newContent .= $afterBlock;

            return $fileManager->filePutContents($this->envFilePath, $newContent);
        }

        return true; // Nothing to remove
    }

    /**
     * Get the current MW environment variables from .env file
     * @return array
     */
    public function getMwEnvVars()
    {
        if (empty($this->envFilePath)) {
            throw new \Exception('Environment file path is not set. Use setWebPath() or setEnvFilePath() first.');
        }

        $fileManager = $this->getFileManager();

        if (!$fileManager->fileExists($this->envFilePath)) {
            return [];
        }

        $existingContent = $fileManager->fileGetContents($this->envFilePath);
        $startPos = strpos($existingContent, self::MW_ENV_START_SEPARATOR);
        $endPos = strpos($existingContent, self::MW_ENV_END_SEPARATOR);

        if ($startPos !== false && $endPos !== false) {
            $mwBlock = substr($existingContent,
                $startPos + strlen(self::MW_ENV_START_SEPARATOR),
                $endPos - $startPos - strlen(self::MW_ENV_START_SEPARATOR)
            );

            return $this->parseEnvVars($mwBlock);
        }

        return [];
    }

    /**
     * Parse environment variables from a string block
     * @param string $envBlock
     * @return array
     */
    private function parseEnvVars($envBlock)
    {
        $vars = [];
        $lines = explode("\n", $envBlock);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
                $value = stripcslashes($value);
            }

            $vars[$key] = $value;
        }

        return $vars;
    }
}
