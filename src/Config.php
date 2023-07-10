<?php

namespace iSemary\BackupSentry;

class Config {
    public $projectPath;
    public $backupPath;

    public function __construct() {
        // Go back to the root project directory
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // Set the root project directory
        $this->projectPath = dirname($reflection->getFileName(), 3);

        $this->backupPath = $this->projectPath . '/' . 'storage/backup-sentry/';
    }
}
