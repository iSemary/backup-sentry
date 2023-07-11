<?php

namespace iSemary\BackupSentry;

class Config {
    public $projectPath;
    public $backupPath;
    public $excludes;
    public $filesBackup;
    public $storagePath;

    public function __construct() {
        // Go back to the root project directory
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // Set the root project directory
        $this->projectPath = dirname($reflection->getFileName(), 3);

        $this->backupPath = $this->projectPath . '/' . 'storage/backup-sentry/';
        
        $this->storagePath = $this->projectPath . '/' . 'storage/';
        
        // IMPORTANT -> to avoid infinity loop you MUST add backup-sentry and .git
        $this->excludes = ['backup-sentry', 'vendor', '.git'];
        

        $this->filesBackup = ['storage', 'full-project', 'tests'];
    }
}
