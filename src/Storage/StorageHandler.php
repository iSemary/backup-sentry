<?php

namespace iSemary\BackupSentry\Storage;

use iSemary\BackupSentry\Config;

class StorageHandler {
    protected $config;
    protected $backupFilesPath;
    public function __construct() {
        $this->config = (new Config);
        $this->backupFilesPath = $this->config->backupPath . 'files/';
    }

    // Backup the complete project 
    public function fullProject() {
        $projectPath = $this->config->projectPath;
        $backupFilesPath = $this->backupFilesPath . 'full-project/full-' . date('Y-m-d-H-i-s');
        // IMPORTANT -> to avoid infinity loop you MUST add backup-sentry 
        $exclude = ['vendor', 'backup-sentry', '.env'];
        // Create backup files directory if not exists
        $this->copyFolderFromTo($projectPath, $backupFilesPath, '', $exclude);
    }

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
