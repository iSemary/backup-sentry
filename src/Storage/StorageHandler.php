<?php

namespace iSemary\BackupSentry\Storage;

use Exception;
use iSemary\BackupSentry\Compress;

class StorageHandler {
    protected $config;
    protected $compress;
    protected $backupFilesPath;
    public function __construct($config) {
        $this->config = $config;
        $this->compress = (new Compress);

        $this->backupFilesPath = $this->config->backupPath . 'files/';
    }

    public function run() {
        $backedUpFiles = [];

        foreach ($this->config->filesBackup as $fileBackup) {
            switch ($fileBackup) {
                case 'storage':
                    $fromDestinationPath = $this->config->storagePath;
                    break;
                case 'full-project':
                    $fromDestinationPath = $this->config->projectPath;
                    break;
                default:
                    $fromDestinationPath = $this->config->projectPath . '/' . $fileBackup;
                    break;
            }

            $backedUpFiles[] = $this->folderBackup((!in_array($fileBackup, ['storage', 'full-project']) ? 'custom' : $fileBackup), $fromDestinationPath)['file_name'];
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => count($this->config->filesBackup) . ' Folders has been created',
            'file_names' => $backedUpFiles
        ];
    }


    public function folderBackup($fileBackupType, $fromDestinationPath) {
        $backupFilesPath = $this->backupFilesPath . ($fileBackupType . '/' . $fileBackupType . '-' . date('Y-m-d-H-i-s'));
        $compressBackupFilesDirectory = $this->config->projectPath . '/storage/backup-sentry/compressed/' . $fileBackupType . '/' . $fileBackupType . '-' . date('Y-m-d-H-i-s') . '.zip';
        if (is_dir($fromDestinationPath)) {
            // copy directory with sub folders
            $this->copyFolderFromTo($fromDestinationPath, $backupFilesPath, '', $this->config->excludes);
        } else {
            // copy single file
            copy($fromDestinationPath, $backupFilesPath . '/' . basename($fromDestinationPath));
        }

        return $this->compress->zip($compressBackupFilesDirectory, $backupFilesPath, $this->config->env->get("BACKUP_SENTRY_ZIP_PASSWORD"), $this->config->configFile['backup']['keep_original_backup_folders'], $this->config->configFile['options']['encryption']);
    }

    // Copy files from directory to another one, with array of excludes
    public function copyFolderFromTo($from, $to, $childFolder = '', $exclude = []) {


        if (is_dir($to) === false) {
            mkdir($to, 0777, true);
        }

        $fromDirectory = opendir($from);

        if ($childFolder !== '') {
            if (is_dir("$to/$childFolder") === false) {
                mkdir("$to/$childFolder", 0777, true);
            }

            while (($file = readdir($fromDirectory)) !== false) {
                // check if the current file is current directory or parent directory
                if ($file === '.' || $file === '..') {
                    continue;
                }
                // If the current file is directory, then re run the function with the directory 
                if (is_dir("$from/$file") === true) {
                    // check if the current directory not listed in the excluded folder
                    if (!in_array(basename("$from/$file"), $exclude)) {
                        $this->copyFolderFromTo("$from/$file", "$to/$childFolder/$file", null, $exclude);
                    }
                } else {
                    // check if the current file not listed in the excluded files
                    if (!in_array("$file", $exclude)) {
                        copy("$from/$file", "$to/$childFolder/$file");
                    }
                }
            }

            closedir($fromDirectory);
            return;
        }


        while (($file = readdir($fromDirectory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // If the current file is directory, then re run the function with the directory 
            if (is_dir("$from/$file") === true) {
                // check if the current directory not listed in the excluded folder
                if (!in_array(basename("$from/$file"), $exclude)) {
                    $this->copyFolderFromTo("$from/$file", "$to/$file", null, $exclude);
                }
            } else {
                // check if the current file not listed in the excluded files
                if (!in_array($file, $exclude)) {
                    copy("$from/$file", "$to/$file");
                }
            }
        }

        closedir($fromDirectory);
    }

    public function cleanUp(array $files = []) {
        try {
            foreach ($files as $key => $file) {
                if (file_exists($file)) unlink($file);
            }
            return [
                'status' => 200,
                'success' => true,
                'message' => 'Clean up successfully finished.'
            ];
        } catch (Exception $e) {
            return [
                'status' => 400,
                'success' => false,
                'message' => 'Failure on cleaning up files.',
                'response' => $e->getMessage()
            ];
        }
    }
}
