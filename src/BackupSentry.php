<?php

namespace iSemary\BackupSentry;

use iSemary\BackupSentry\Cloud\AWS;
use iSemary\BackupSentry\Cloud\GoogleDrive;
use iSemary\BackupSentry\DB\Export;
use iSemary\BackupSentry\Logger\Log;
use iSemary\BackupSentry\Mail\Mail;
use iSemary\BackupSentry\Storage\StorageHandler;

class BackupSentry {
    protected $config;
    protected array $files;
    protected string $backupFileName;
    protected string $backupFilePath;
    protected string $compressedFile;

    public function __construct() {
        $this->config = new Config;
        $this->backupFileName = 'backup-sentry-' . date('Y-m-d-H-i-s') . '.zip';
        $this->backupFilePath = $this->config->backupPath . 'complete/';
    }

    public function run() {
        $logs = [];
        $errors = [];

        $exportedCompressedFiles = [];

        // export database
        if ($this->config->configFile['backup']['database']['allow']) {
            $exportDB = $this->exportDB();
            $logs[] = $exportDB;

            if ($exportDB['status'] == 200) {
                $exportedCompressedFiles[] = $exportDB['file_name'];
            } else {
                $errors[] = $exportDB['message'];
            }
        }

        // export files / folders
        $exportFiles = $this->exportFiles();
        $logs[] = $exportFiles;

        // append the exported files/folders into exported files
        if ($exportFiles['status'] == 200) {
            $exportedCompressedFiles = array_merge($exportedCompressedFiles, $exportFiles['file_names']);
        } else {
            $errors[] = $exportFiles['message'];
        }

        // append the complete backup file into exported files
        foreach ($exportedCompressedFiles as $exportedCompressedFile) {
            if (file_exists($exportedCompressedFile)) {
                $logs[] = $this->compressExportFile($exportedCompressedFile);
            }
        }

        $exportedCompressedFiles[] = $this->backupFilePath . $this->backupFileName;

        // store compressed file to google drive if enabled
        if ($this->config->cloud['google_drive']['allow']) {
            $uploadToGoogleDrive = $this->uploadToGoogleDrive($this->backupFilePath . $this->backupFileName);
            if ($uploadToGoogleDrive['status'] != 200) {
                $errors[] = $uploadToGoogleDrive['message'] . " | " . $uploadToGoogleDrive['response'];
            }
            $logs[] = $uploadToGoogleDrive;
        }

        // store compressed file to AWS if enabled
        if ($this->config->cloud['aws']['allow']) {
            $uploadToAWS = $this->uploadToAWS();
            if ($uploadToAWS['status'] != 200) {
                $errors[] = $uploadToAWS['message'] . " | " . $uploadToAWS['response'];
            }
            $logs[] = $uploadToAWS;
        }


        // clean up compressed files
        if ($this->config->configFile['backup']['cleanup']) {
            $cleanUp = $this->cleanUp($exportedCompressedFiles);
            if ($cleanUp['status'] != 200) {
                $errors[] = $cleanUp['message'] . ' | ' . $cleanUp['response'];
            }
            $log[] = $cleanUp;
        }

        // send email alert 
        $sendLogMail = $this->sendLogMail((count($errors) == 0 ? true : false), count($errors) == 0 ? $errors : false);
        if ($sendLogMail['status'] != 200) {
            $errors[] = $sendLogMail['message'] . ' | ' . $sendLogMail['response'];
        }
        $log[] = $sendLogMail;

        // write out the logging data
        $this->writeLogFile($logs);
    }

    private function exportDB() {
        return (new Export($this->config))->run();
    }

    private function exportFiles() {
        return (new StorageHandler($this->config))->run();
    }

    private function compressExportFile($exportedCompressedFile) {
        return (new Compress($this->config))->zip($this->backupFilePath . $this->backupFileName, $exportedCompressedFile, $this->config->zipPassword);
    }

    private function uploadToGoogleDrive() {
        return (new GoogleDrive($this->config))->upload($this->backupFilePath . $this->backupFileName);
    }

    private function uploadToAWS() {
        return (new AWS($this->config))->upload($this->backupFilePath . $this->backupFileName);
    }

    private function sendLogMail($status, $content) {
        return (new Mail($this->config))->send($status, $content);
    }

    private function writeLogFile($message) {
        return (new Log)->write($message, $this->config->logFile);
    }

    private function cleanUp(array $files = []) {
        return (new StorageHandler($this->config))->cleanUp($files);
    }
}
