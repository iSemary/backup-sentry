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
    public object $channels;
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
            $configFile = require $configFileLocation;
            $this->configFile = $configFile;
        } else {
            $this->configFile = null;
        }

        $this->storagePath = $this->projectPath . '/' . $this->configFile['backup']['storage_path'];

        $this->backupPath = $this->storagePath . 'backup-sentry/';

        // IMPORTANT -> to avoid infinity loop you MUST add backup-sentry and .git
        $this->excludes = [
            $this->configFile['backup']['excludes'],
            'backup-sentry'
        ];


        $this->logFile = $this->backupPath . 'log/log-' . date('Y-m-d') . '.log';

        /**
         * Initialize Channels Object
         */
        $this->channels = new \stdClass();
        // Slack
        $this->channels->slack = new \stdClass();
        $this->channels->slack->allow = $this->configFile['backup']['channels']['slack']['allow'];
        $this->channels->slack->WebhookURL = self::returnEnvValueIfNotExistsInConfig($this->configFile['backup']['channels']['slack']['webhook_url'], 'SLACK_WEBHOOK_URL');
        // Telegram
        $this->channels->telegram = new \stdClass();
        $this->channels->telegram->allow = $this->configFile['backup']['channels']['telegram']['allow'];
        $this->channels->telegram->botToken = self::returnEnvValueIfNotExistsInConfig($this->configFile['backup']['channels']['telegram']['bot_token'], 'TELEGRAM_BOT_TOKEN');
        $this->channels->telegram->chatIDs = $this->configFile['backup']['channels']['telegram']['chat_ids'];



        // Folders/Files configuration
        $filesBackup = [];

        $this->configFile['backup']['full_project'] ? $filesBackup[] = "full-project" : null;
        $this->configFile['backup']['storage_only'] ? $filesBackup[] = "storage" : null;
        count($this->configFile['backup']['specific_folders_or_files']) ? $filesBackup = array_merge($filesBackup, $this->configFile['backup']['specific_folders_or_files']) : null;

        $this->filesBackup = $filesBackup;


        // Database configuration
        $this->db = [
            'allow' => $configFile['backup']['database']['allow'],
            'connection' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['database']['connection'], 'DB_CONNECTION'),
            'host' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['database']['host'], 'DB_HOST'),
            'port' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['database']['port'], 'DB_PORT'),
            'name' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['database']['name'], 'DB_DATABASE'),
            'username' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['database']['username'], 'DB_USERNAME'),
            'password' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['database']['password'], 'DB_PASSWORD'),
        ];
        // Cloud configuration
        $this->cloud = [
            'google_drive' => [
                'allow' => $configFile['backup']['cloud_services']['google_drive']['allow'],
                'folder_id' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['folder_id'], 'GOOGLE_DRIVE_BACKUP_FOLDER_ID'),
                'client_id' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['client_id'], 'GOOGLE_DRIVE_CLIENT_ID'),
                'client_secret' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['client_secret'], 'GOOGLE_DRIVE_CLIENT_SECRET'),
                'refresh_token' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['google_drive']['refresh_token'], 'GOOGLE_DRIVE_REFRESH_TOKEN'),
            ],
            'aws' => [
                'allow' => $configFile['backup']['cloud_services']['google_drive']['allow'],
                'access_key' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['access_key'], 'AWS_ACCESS_KEY_ID'),
                'secret_key' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['secret_key'], 'AWS_SECRET_ACCESS_KEY'),
                'bucket_name' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['bucket_name'], 'AWS_BUCKET'),
                'region' => self::returnEnvValueIfNotExistsInConfig($configFile['backup']['cloud_services']['aws']['region'], 'AWS_DEFAULT_REGION'),
            ]
        ];
    }

    /**
     * The function returns a value if it exists and is not empty, otherwise it retrieves the value from
     * the environment using a specified key.
     * 
     * @param value The value parameter is the value that you want to check if it exists and is not empty.
     * @param envKey The `envKey` parameter is a string that represents the key of the environment variable
     * that you want to retrieve.
     * 
     * @return the value of `` if it is set and not empty. Otherwise, it returns the value of env
     */
    private function returnEnvValueIfNotExistsInConfig($value, $envKey) {
        return (isset($value) && !empty($value)) ? $value : $this->env->get($envKey);
    }
}
