<?php

namespace iSemary\BackupSentry\DB;

use iSemary\BackupSentry\Compress;

class Export {
    private $config;
    protected $compress;

    public function __construct($config) {
        $this->config = $config;
        $this->compress = (new Compress);
    }

    /**
     * The function exports a database based on user configuration and returns a status, success message,
     * and file name.
     * 
     * @return array an array with the following keys:
     * - "status": The Status of the method process
     * - "success": A boolean indicating whether the file upload was successful or not.
     * - "message": A message describing the result of the file upload.
     * - "file_name": The exported file name
     * - "response": The exception object
     *      */
    public function run():array {
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

                $dateTime = date('Y-m-d-H-i-s');
                // set the filename for the db file
                $databaseFile = $dir . "backup-$dateTime.sql";

                switch ($this->config->db['connection']) {
                    case 'mysql':
                        // MySQL database configuration
                        $command = "/usr/bin/mysqldump --opt --host={$this->config->db['host']} --user={$this->config->db['username']} --password='{$this->config->db['password']}' {$this->config->db['name']} > {$databaseFile}";
                        break;
                    case 'mongodb';
                        // MongoDB database configuration
                        $command = "/usr/bin/mongodump --host {$this->config->db['host']} --port {$this->config->db['port']} --username {$this->config->db['username']} --password '{$this->config->db['password']}' --db {$this->config->db['name']} --out $databaseFile";
                    case 'pgsql';
                        // PostgreSQL database configuration
                        $command = "/usr/bin/pg_dump --host=$this->config->db['host']} --port={$this->config->db['port']} --username={$this->config->db['username']} --password='{$this->config->db['password']}' --dbname={$this->config->db['name']} --file=$databaseFile";
                    default:
                        break;
                }
                // execute export command line
                shell_exec($command);
                // compress the database file
                $compressBackupFilesDirectory = $this->config->projectPath . "/storage/backup-sentry/compressed/db/db-$dateTime.zip";
                $filename = $this->compress->zip($compressBackupFilesDirectory, $databaseFile, $this->config->compressedPassword, $this->config->configFile['backup']['keep_original_backup_folders'], $this->config->configFile['options']['encryption'])['file_name'];

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
