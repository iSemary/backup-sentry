<?php

namespace iSemary\BackupSentry;

use iSemary\BackupSentry\Env\EnvHandler;

class Config {
    public $projectPath;
    public $backupPath;
    public $env;
    public $db;

    public function __construct() {
        // Go back to the root project directory
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // Set the root project directory
        $this->projectPath = dirname($reflection->getFileName(), 3);

        $this->backupPath = $this->projectPath . '/' . 'storage/backup-sentry/';

        $this->env = new EnvHandler;

        $this->db = [
            'connection'=> $config['data'],
            'host'=> $config['data'],
            'port'=> $config['data'],
            'name'=> $config['data'],
            'username'=> $config['data'],
            'password'=> $config['data'],
        ];
    }
}
