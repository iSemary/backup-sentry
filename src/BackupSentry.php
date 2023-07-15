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
        $log = [];
        $exportedCompressedFiles = [];

        $exportDB = $this->exportDB();
        $log[] = $exportDB;

        if ($exportDB['status'] == 200) $exportedCompressedFiles[] = $exportDB['file_name'];

        $exportFiles = $this->exportFiles();
        $log[] = $exportFiles;

        if ($exportFiles['status'] == 200) $exportedCompressedFiles = array_merge($exportedCompressedFiles, $exportFiles['file_names']);



        foreach ($exportedCompressedFiles as $exportedCompressedFile) {
            if (file_exists($exportedCompressedFile)) {
                $log[] = $this->compressExportFile($exportedCompressedFile);
            }
        }


        $this->writeLogFile($log);

        $uploadToGoogleDrive = $this->uploadToGoogleDrive($this->backupFilePath . $this->backupFileName);
        $log[] = $uploadToGoogleDrive;


        die(print_r(""));
        // $uploadToAWS = $this->uploadToAWS();
        // $log[] = $uploadToAWS;

        // $sendLogMail = $this->sendLogMail();
        // $log[] = $sendLogMail;

        $cleanUp = $this->cleanUp($exportedCompressedFiles); // clean up compressed files

        $this->writeLogFile($log);
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
        return (new GoogleDrive($this->config))->upload($this->compressedFile);
    }

    private function uploadToAWS() {
        return (new AWS($this->config))->upload($this->compressedFile);
    }

    private function sendLogMail($status, $to, $subject, $message) {
        return (new Mail($this->config))->send($status, $to, $subject, $message);
    }

    private function writeLogFile($message) {
        return (new Log)->write($message, $this->config->logFile);
    }

    private function cleanUp(array $files = []) {
        return (new StorageHandler($this->config))->cleanUp($files);
    }
}
