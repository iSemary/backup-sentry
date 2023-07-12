<?php

namespace iSemary\BackupSentry\DB;

class Export {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function run() {


        if ($this->config->db['allow']) {

            try {
                $dir = $this->config->backupPath . 'db/';
                // create backup directory if not exists
                if (!is_dir($dir)) {
                    try {
                        mkdir($dir, 0777, true);
                    } catch (\Exception $e) {
                        return [
                            'status' => 400,
                            'success' => false,
                            'message' => "Permission denied while creating database backup directory : " . $e->getMessage(),
                            'file_name' => null,
                        ];
                    }
                }

                // set the filename for the db file
                $filename = $dir . 'backup-' . date('Y-m-d-His') . '.sql';

                switch ($this->config->db['connection']) {
                    case 'mysql':
                        // MySQL database configuration
                        $command = "/usr/bin/mysqldump --opt --host={$this->config->db['host']} --user={$this->config->db['username']} --password='{$this->config->db['password']}' {$this->config->db['name']} > {$filename}";
                        break;
                    case 'mongodb';
                        // MongoDB database configuration
                        $command = "/usr/bin/mongodump --host {$this->config->db['host']} --port {$this->config->db['port']} --username {$this->config->db['username']} --password '{$this->config->db['password']}' --db {$this->config->db['name']} --out $filename";
                    case 'pgsql';
                        // PostgreSQL database configuration
                        $command = "/usr/bin/pg_dump --host=$this->config->db['host']} --port={$this->config->db['port']} --username={$this->config->db['username']} --password='{$this->config->db['password']}' --dbname={$this->config->db['name']} --file=$filename";
                    default:
                        break;
                }

                shell_exec($command);

                return [
                    'status' => 200,
                    'success' => true,
                    'message' => "Database Exported Successfully.",
                    'file_name' => $filename,
                ];
            } catch (\Exception $e) {
                return [
                    'status' => 400,
                    'success' => false,
                    'message' => "Fail on exporting database : " . $e->getMessage(),
                    'file_name' => null,
                ];
            }
        } else {
            return [
                'status' => 201,
                'success' => true,
                'message' => "Database not exported base on user configuration.",
                'file_name' => null,
            ];
        }
    }
}
