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

    public $zipPassword;
    public $db;
    public array $cloud;
    public $logFile;

    public function __construct() {
        $this->env = new EnvHandler;
        // Go back to the root project directory
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // Set the root project directory
        $this->projectPath = dirname($reflection->getFileName(), 3);

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

        $this->storagePath = $this->projectPath . '/' . $this->configFile['backup']['storage_path'];

        $this->backupPath = $this->storagePath . 'backup-sentry/';

        // IMPORTANT -> to avoid infinity loop you MUST add backup-sentry and .git
        $this->excludes = ['backup-sentry', 'vendor', '.git'];


        $this->logFile = $this->backupPath . 'log/log-' . date('Y-m-d') . '.log';



        // Folders/Files configuration
        $filesBackup = [];

        $this->configFile['backup']['full_project'] ? $filesBackup[] = "full-project" : null;
        $this->configFile['backup']['storage_only'] ? $filesBackup[] = "storage" : null;
        count($this->configFile['backup']['specific_folders_or_files']) ? $filesBackup = array_merge($filesBackup, $this->configFile['backup']['specific_folders_or_files']) : null;

        $this->filesBackup = $filesBackup;


        // Database configuration
        $this->db = [
            'allow' => $configFile['backup']['database']['allow'],
            'connection' => $configFile['backup']['database']['connection'],
            'host' => $configFile['backup']['database']['host'],
            'port' => $configFile['backup']['database']['port'],
            'name' => $configFile['backup']['database']['name'],
            'username' => $configFile['backup']['database']['username'],
            'password' => $configFile['backup']['database']['password'],
        ];

        $this->cloud = [
            'google_drive' => [
                'allow' => true,
                'folder_id' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['folder_id'], 'GOOGLE_BACKUP_FOLDER_ID'),
                'client_id' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['client_id'], 'GOOGLE_DRIVE_CLIENT_ID'),
                'client_secret' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['client_secret'], 'GOOGLE_DRIVE_CLIENT_SECRET'),
            ],
            'aws' => [
                'allow' => true,
                'access_key' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['access_key'], 'AWS_ACCESS_KEY_ID'),
                'secret_key' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['secret_key'], 'AWS_SECRET_ACCESS_KEY'),
                'bucket_name' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['bucket_name'], 'AWS_BUCKET'),
            ]
        ];
    }

    private function returnEnvValueIfNotExistsInConfig($value, $envKey) {
        return (isset($value) && !empty($value)) ? $value : $this->env->get($envKey);
    }
}
