<?php

namespace iSemary\BackupSentry;

use iSemary\BackupSentry\Cloud\AWS;
use iSemary\BackupSentry\Cloud\GoogleDrive;
use iSemary\BackupSentry\DB\Export;
use iSemary\BackupSentry\Logger\Log;
use iSemary\BackupSentry\Mail\Mail;
use iSemary\BackupSentry\Storage\StorageHandler;
use iSemary\BackupSentry\Channels\Slack;
use iSemary\BackupSentry\Channels\Telegram;

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

    // The main method responsible for executing the backup process.
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
        if ($this->config->configFile['backup']['mail']['allow']) {
            $sendLogMail = $this->sendLogMail((count($errors) == 0 ? true : false), count($errors) == 0 ? $errors : false);
            if ($sendLogMail['status'] != 200) {
                $errors[] = $sendLogMail['message'] . ' | ' . $sendLogMail['response'];
            }
            $log[] = $sendLogMail;
        }

        // send slack alert 
        if ($this->config->channels->slack->allow) {
            $sendLogSlack = $this->sendLogSlack(count($errors) == 0 ? $errors : false);
            if ($sendLogSlack['status'] != 200) {
                $errors[] = $sendLogSlack['message'] . ' | ' . $sendLogSlack['response'];
            }
            $log[] = $sendLogSlack;
        }

        // send telegram alert 
        if ($this->config->channels->telegram->allow) {
            $sendLogTelegram = $this->sendLogTelegram(count($errors) == 0 ? $errors : false);
            $log[] = $sendLogTelegram;
        }

        // write out the logging data
        $this->writeLogFile($logs);
    }

    // Export the database using the configured settings.
    private function exportDB() {
        return (new Export($this->config))->run();
    }

    // Export files/folders using the configured storage settings.
    private function exportFiles() {
        return (new StorageHandler($this->config))->run();
    }

    // Compress the exported data into a zip file.
    private function compressExportFile($exportedCompressedFile) {
        return (new Compress($this->config))->zip($this->backupFilePath . $this->backupFileName, $exportedCompressedFile, $this->config->zipPassword);
    }

    // Upload the backup file to Google Drive if enabled in the configuration.
    private function uploadToGoogleDrive() {
        return (new GoogleDrive($this->config))->upload($this->backupFilePath . $this->backupFileName);
    }

    // Upload the backup file to AWS if enabled in the configuration.
    private function uploadToAWS() {
        return (new AWS($this->config))->upload($this->backupFilePath . $this->backupFileName);
    }

    // Send email alert with backup log and error status if applicable.
    private function sendLogMail($status, $content) {
        return (new Mail($this->config))->send($status, $content);
    }

    // Send Slack alert with backup log and status (success/failure).
    private function sendLogSlack($content) {
        return (new Slack($this->config))->send(($content ? json_encode($content) : "Successful backup " . date("d F Y")), "Backup Sentry | " . ($content ? "Failure Backup" : "Successful backup"), ($content ? ":rotating_light:" : ":white_check_mark:"));
    }

    // Send Telegram alert with backup log and error status if applicable.
    private function sendLogTelegram($content) {
        $log = [];
        $chatIDs = $this->config->channels->telegram->chatIDs;
        $content = $content ? json_encode($content) : "Successful backup " . date("d F Y");

        foreach ($chatIDs as $chatID) {
            $log[] = (new Telegram($this->config))->send($chatID, $content);
        }

        return $log;
    }

    // Write the logging data to the specified log file.
    private function writeLogFile($message) {
        return (new Log)->write($message, $this->config->logFile);
    }

    // Clean up compressed backup files based on the provided array.
    private function cleanUp(array $files = []) {
        return (new StorageHandler($this->config))->cleanUp($files);
    }
}
