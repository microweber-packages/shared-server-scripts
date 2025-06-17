# Microweber Shared Server Scripts

A comprehensive PHP package for managing Microweber CMS installations, downloads, templates, modules, and server operations. This package provides a unified interface for server-side operations commonly needed when managing multiple Microweber websites.

## Overview

The Microweber Shared Server Scripts package provides automated tools for:
- Installing and managing Microweber CMS instances
- Downloading core files, templates, and modules
- Managing environment variables and configurations
- Scanning and monitoring installations
- Handling whitelabel and WHMCS integrations

## Requirements

- PHP >= 8.3
- Symfony Process component
- Symfony Filesystem component
- Microweber Composer Client package

## Installation

```bash
composer require microweber-packages/shared-server-scripts
```

## Core Classes

### Installation & Management

#### `MicroweberInstaller`
Handles fresh installations of Microweber CMS with support for both standalone and symlink installations.

**Key Features:**
- Supports MySQL and SQLite databases
- Configurable admin credentials
- Template and language selection
- Standalone or symlinked installations
- Automatic directory structure creation

**Usage:**
```php
use MicroweberPackages\SharedServerScripts\MicroweberInstaller;

$installer = new MicroweberInstaller();
$installer->setPath('/path/to/website');
$installer->setSourcePath('/path/to/microweber/source');
$installer->setDatabaseDriver('mysql');
$installer->setDatabaseHost('localhost');
$installer->setDatabaseName('my_database');
$installer->setDatabaseUsername('user');
$installer->setDatabasePassword('password');
$installer->setAdminEmail('admin@example.com');
$installer->setAdminUsername('admin');
$installer->setAdminPassword('password');
$installer->run();
```

#### `MicroweberReinstaller`
Extends MicroweberInstaller to handle reinstallation scenarios while preserving user data and configurations.

**Key Features:**
- Preserves user files and configurations
- Updates core files only
- Supports both symlink and standalone reinstalls
- Maintains file ownership and permissions

#### `MicroweberUninstaller`
Safely removes Microweber installations while providing detailed feedback on the removal process.

**Key Features:**
- Selective file and directory removal
- Error handling and logging
- Preserves user data if needed
- Complete cleanup of core files

### Download & Content Management

#### `MicroweberDownloader`
Downloads the latest Microweber CMS releases from official sources.

**Key Features:**
- Stable and development release support
- Automatic validation of downloads
- Integration with Composer client
- Configurable download sources

**Usage:**
```php
use MicroweberPackages\SharedServerScripts\MicroweberDownloader;

$downloader = new MicroweberDownloader();
$downloader->setReleaseSource(MicroweberDownloader::STABLE_RELEASE);
$downloader->download('/path/to/download/location');
```

#### `MicroweberTemplatesDownloader`
Downloads and manages Microweber templates from the official repository.

**Key Features:**
- Composer-based template discovery
- Automatic template installation
- Version management
- Bulk template downloads

#### `MicroweberModuleConnectorsDownloader`
Handles downloading and installation of Microweber modules and connectors.

**Key Features:**
- Module discovery via Composer
- Automatic dependency resolution
- Version compatibility checking
- License validation

### Environment & Configuration

#### `MicroweberEnvFileWebsiteApply`
Manages environment variables in .env files with support for specific section isolation.

**Key Features:**
- Section-based environment variable management
- Automatic value escaping and validation
- Selective updates without affecting other configurations
- Backup and restore capabilities

**Usage:**
```php
use MicroweberPackages\SharedServerScripts\MicroweberEnvFileWebsiteApply;

$envApply = new MicroweberEnvFileWebsiteApply();
$envApply->setWebPath('/path/to/website');

// Apply environment variables
$envVars = [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'my_database',
    'APP_URL' => 'https://example.com'
];
$envApply->applyEnvVars($envVars);

// Get current MW env vars
$currentVars = $envApply->getMwEnvVars();

// Remove MW env vars
$envApply->removeMwEnvVars();
```

### Analysis & Monitoring

#### `MicroweberAppPathHelper`
Provides detailed information about Microweber installations and their capabilities.

**Key Features:**
- Installation detection and validation
- Version information retrieval
- Module and template discovery
- Language support detection
- Admin token generation
- Application details extraction

**Usage:**
```php
use MicroweberPackages\SharedServerScripts\MicroweberAppPathHelper;

$helper = new MicroweberAppPathHelper();
$helper->setPath('/path/to/installation');

$isInstalled = $helper->isInstalled();
$version = $helper->getCurrentVersion();
$modules = $helper->getSupportedModules();
$templates = $helper->getSupportedTemplates();
$languages = $helper->getSupportedLanguages();
```

#### `MicroweberInstallationsScanner`
Recursively scans directories to find and analyze Microweber installations.

**Key Features:**
- Recursive directory scanning
- Installation validation
- Bulk installation analysis
- Symlink detection
- Installation metadata extraction

### Integration & White-labeling

#### `MicroweberWhitelabelWebsiteApply`
Applies white-label branding configurations to Microweber installations.

**Key Features:**
- Branding file management
- Symlink-based branding application
- Configuration synchronization

#### `MicroweberWhitelabelSettingsUpdater`
Updates white-label settings and configurations for SaaS deployments.

**Key Features:**
- JSON-based settings management
- Storage directory handling
- Settings validation and formatting

#### `MicroweberWhmcsConnector`
Integrates Microweber installations with WHMCS billing systems.

**Key Features:**
- WHMCS API integration
- Domain-based configuration
- Template selection from WHMCS
- White-label settings retrieval
- Automated configuration application

## Shared Components

### Traits

#### `MicroweberFileOperationsTrait`
Provides common file operations and path management functionality used across all classes.

**Key Features:**
- File manager and shell executor initialization
- Version detection (v2 vs v3)
- Directory structure management
- File operation helpers
- Permission and ownership handling

### Adapters

#### File Management
- **`NativeFileManager`**: Standard filesystem operations
- **`PleskDomainFileManager`**: Plesk domain-specific file operations
- **`PleskServerFileManager`**: Plesk server-level file operations

#### Shell Execution
- **`NativeShellExecutor`**: Standard shell command execution
- **`PleskShellExecutor`**: Plesk-specific shell operations with proper environment handling

### Interfaces

#### `IMicroweberDownloader`
Defines the contract for download operations, ensuring consistent implementation across different download strategies.

## Shell Scripts

The package includes several shell scripts for system-level operations:

- **`check_disk_space.sh`**: Monitors available disk space
- **`chown_installed_app.sh`**: Sets proper file ownership for installed applications
- **`unzip_app_module.sh`**: Extracts module archives
- **`unzip_app_template.sh`**: Extracts template archives  
- **`unzip_app_version.sh`**: Extracts version-specific archives

## Testing

The package includes comprehensive PHPUnit tests:

```bash
vendor/bin/phpunit
```

Test files:
- `MicroweberDownloaderTest.php`: Tests download functionality
- `MicroweberInstallerTest.php`: Tests installation processes

## Configuration

### Environment Variables

The package supports the following environment variable patterns in .env files:

```env
# Regular application settings
APP_NAME=MyApp
DB_CONNECTION=mysql

### MW ENV VARS APPLY ###
SOME_VAR=text@example.com
SOME_VAR_2=text@example.com
### END MW ENV VARS APPLY ###

# Other settings continue here
```

### Database Support

- **MySQL**: Full support with configurable host, port, credentials
- **SQLite**: Automatic database file creation and management

### Installation Types

- **Standalone**: Complete file copy installation
- **Symlink**: Symbolic link-based installation for shared hosting

## Error Handling

All classes implement comprehensive error handling with:
- Detailed exception messages
- Operation logging
- Rollback capabilities where applicable
- Validation at each step

## Best Practices

1. **Always validate paths** before performing operations
2. **Use appropriate adapters** for your hosting environment
3. **Check permissions** before file operations
4. **Backup configurations** before making changes
5. **Test installations** in development environments first

## License

MIT License - See LICENSE file for details

## Support

For support, please contact: support@microweber.com

## Contributing

Contributions are welcome! Please ensure all tests pass and follow the existing code style.

---

*This package is part of the Microweber CMS ecosystem and is designed for server administrators and hosting providers managing multiple Microweber installations.*