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
        $this->backupFileName = 'backup-sentry-' . date('YmdHis');
        $this->backupFilePath = $this->config->backupPath . 'complete/';
    }

    public function run() {
        $log = [];
        $exportedFiles = [];

        $exportDB = $this->exportDB();
        $log[] = $exportDB;

        if ($exportDB['status'] == 200) $exportedFiles[] = $exportDB['file_name'];


        $exportFiles = $this->exportFiles();
        $log[] = $exportFiles;
        
        die(var_dump($exportFiles));

        if ($exportFiles['status'] == 200) array_merge($exportedFiles, $exportFiles['file_names']);



        $compressExportFile = $this->compressExportFile();

        $uploadToGoogleDrive = $this->uploadToGoogleDrive();

        $uploadToAWS = $this->uploadToAWS();

        $sendLogMail = $this->sendLogMail();

        $cleanUp = $this->cleanUp();

        $this->writeLogFile($log);
    }

    private function exportDB() {
        return (new Export($this->config))->run();
    }

    private function exportFiles() {
        return (new StorageHandler($this->config))->run();
    }

    private function compressExportFile() {
        return (new Compress($this->config))->zip($this->backupFileName, $this->backupFilePath, $this->config->zipPassword);
    }

    private function uploadToGoogleDrive() {
        return (new GoogleDrive($this->config))->upload($this->compressedFile);
    }

    private function uploadToAWS() {
        return (new AWS($this->config))->upload($this->compressedFile);
    }

    private function sendLogMail($status, $to, $subject, $message) {
        return (new Mail($this->config))->send($status, $to, $subject, $message);
    }

    private function writeLogFile() {
        return (new Log)->run();
    }

    private function cleanUp() {
        return (new StorageHandler)->cleanUp();
    }
}
