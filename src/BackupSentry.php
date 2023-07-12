<?php

namespace iSemary\BackupSentry;

use iSemary\BackupSentry\Cloud\AWS;
use iSemary\BackupSentry\Cloud\GoogleDrive;
use iSemary\BackupSentry\DB\Export;
use iSemary\BackupSentry\Mail\Mail;
use iSemary\BackupSentry\Storage\StorageHandler;

class BackupSentry {
    protected $config;
    protected array $files;
    protected string $backupFileName;
    protected string $backupFilePath;
    protected string $compressedFile;

    public function __construct() {
        $this->config = (new Config);
        $this->backupFileName = 'backup-sentry-' . date('YmdHis');
        $this->backupFilePath = $this->config->backupPath . 'complete/';
    }

    public function run() {
    }

    private function exportDB() {
        return (new Export)->run();
    }

    private function exportFiles() {
        return (new StorageHandler)->run();
    }

    private function compressExportFile() {
        return (new Compress)->zip($this->backupFileName, $this->backupFilePath, $this->config->zipPassword);
    }

    private function uploadToGoogleDrive() {
        return (new GoogleDrive)->upload($this->compressedFile);
    }

    private function uploadToAWS() {
        return (new AWS)->upload($this->compressedFile);
    }

    private function sendLogMail($status, $to, $subject, $message) {
        return (new Mail)->send($status, $to, $subject, $message);
    }

    private function cleanUp() {
        return (new StorageHandler)->cleanUp();
    }
}
