<?php

namespace iSemary\BackupSentry\Storage;

use iSemary\BackupSentry\Compress;
use iSemary\BackupSentry\Config;
use iSemary\BackupSentry\Env\EnvHandler;

class StorageHandler {
    protected $env;
    protected $config;
    protected $compress;
    protected $backupFilesPath;
    public function __construct() {
        $this->config = (new Config);
        $this->env = (new EnvHandler);
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

            $file[] =  $this->folderBackup((!in_array($fileBackup, ['storage', 'full-project']) ? 'custom' : $fileBackup), $fromDestinationPath);
        }
    }


    public function folderBackup($fileBackup, $fromDestinationPath) {
        $backupFilesPath = $this->backupFilesPath . ($fileBackup . '/' . $fileBackup . '-' . date('Y-m-d-H-i-s'));
        $this->copyFolderFromTo($fromDestinationPath, $backupFilesPath, '', $this->config->excludes);
        $this->compress->zip($backupFilesPath, $backupFilesPath, $this->env->get("BACKUP_SENTRY_ZIP_PASSWORD"));
    }

    // Copy files from directory to another one, with array of excludes
    public function copyFolderFromTo($from, $to, $childFolder = '', $exclude = []) {

        $fromDirectory = opendir($from);

        if (is_dir($to) === false) {
            mkdir($to);
        }

        if ($childFolder !== '') {
            if (is_dir("$to/$childFolder") === false) {
                mkdir("$to/$childFolder");
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
}
