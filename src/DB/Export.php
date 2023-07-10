<?php

namespace iSemary\BackupSentry\DB;

use iSemary\BackupSentry\Config;
use iSemary\BackupSentry\Env\EnvHandler;

class Export {

    public function run() {
        $config = new Config;
        $env = new EnvHandler;

        try {
            $dir = $config->projectPath . $config->backupPath . 'db/';
            // create backup directory if not exists
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            // set the filename for the SQL file
            $filename = $dir . 'backup-' . date('Y-m-d-His') . '.sql';

            switch ($env->get('DB_CONNECTION')) {
                case 'mysql':
                    // MySQL database configuration
                    // execute the mysqldump command and save the output to a file
                    $cmd = "/usr/bin/mysqldump";
                    $command = "$cmd --opt --host={$env->get('DB_HOST')} --user={$env->get('DB_USERNAME')} --password='{$env->get('DB_PASSWORD')}' {$env->get('DB_DATABASE')} > {$filename}";
                    break;
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
