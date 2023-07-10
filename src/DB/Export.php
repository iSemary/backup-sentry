<?php

namespace iSemary\BackupSentry\DB;

use iSemary\BackupSentry\Config;
use iSemary\BackupSentry\Env\EnvHandler;

class Export {
    private $config;

    public function __construct() {
        $this->config = new Config;
    }

    public function run() {

        try {
            $dir = $this->config->projectPath . $this->config->backupPath . 'db/';
            // create backup directory if not exists
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            // set the filename for the SQL file
            $filename = $dir . 'backup-' . date('Y-m-d-His') . '.sql';

            switch ($this->config->db['connection']) {
                case 'mysql':
                    // MySQL database configuration
                    // execute the mysqldump command and save the output to a file
                    $cmd = "/usr/bin/mysqldump";
                    $command = "$cmd --opt --host={$this->config->db['host']} --user={$this->config->db['username']} --password='{$this->config->db['password']}' {$this->config->db['name']} > {$filename}";
                    break;

                case 'mongodb';
                    $cmd = "mongodump";
                    $command = "$cmd --host {$this->config->db['host']} --port {$this->config->db['port']} --username {$this->config->db['username']} --password {$this->config->db['password']} --db {$this->config->db['name']} --out $filename";

                default:
                    break;
            }

            shell_exec($command);

            return [
                'success' => true,
                'message' => "Database Exported Successfully.",
                'file_name' => $filename,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Fail on exporting database : " . $e->getMessage(),
                'file_name' => null,
            ];
        }
    }
}
