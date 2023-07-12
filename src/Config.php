<?php

namespace iSemary\BackupSentry;

use iSemary\BackupSentry\Env\EnvHandler;

class Config {
    public $env;
    public $configFile;
    public $projectPath;
    public $backupPath;
    public $excludes;
    public $filesBackup;
    public $storagePath;
    // AWS
    public $accessKey;
    public $secretKey;
    public $bucketName;
    // Google Drive
    public $googleDriveClientID;
    public $googleDriveClientSecret;
    public $googleDriveRefreshToken;
    public $googleDriveFolderID;

    public $zipPassword;
    public $db;

    public function __construct() {
        $this->env = new EnvHandler;
        // Go back to the root project directory
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // Set the root project directory
        $this->projectPath = dirname($reflection->getFileName(), 3);

        $this->backupPath = $this->projectPath . '/' . 'storage/backup-sentry/';

        $this->storagePath = $this->projectPath . '/' . 'storage/';

        // IMPORTANT -> to avoid infinity loop you MUST add backup-sentry and .git
        $this->excludes = ['backup-sentry', 'vendor', '.git'];

        $this->filesBackup = ['storage', 'full-project', 'tests'];

        $this->zipPassword = $this->env->get('BACKUP_SENTRY_ZIP_PASSWORD');

        /**
         * Load user config options 
         */
        $configFileLocation = $this->projectPath . '/config/BackupSentry.php';
        if (file_exists($configFileLocation)) {
            $configFile = require_once $configFileLocation;
            $this->configFile = $configFile;
        } else {
            $this->configFile = null;
        }

        $this->db = [
            'allow' => $configFile['backup']['database']['allow'],
            'connection' => $configFile['backup']['database']['connection'],
            'host' => $configFile['backup']['database']['host'],
            'port' => $configFile['backup']['database']['port'],
            'name' => $configFile['backup']['database']['name'],
            'username' => $configFile['backup']['database']['username'],
            'password' => $configFile['backup']['database']['password'],
        ];



        $this->googleDriveFolderID = $this->env->get('GOOGLE_BACKUP_FOLDER_ID');
    }
}
