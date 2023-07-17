<?php

namespace iSemary\BackupSentry;

class Compress {

    /**
     * The function `zip` compresses a folder into a zip archive, encrypts the files with a password, and
     * optionally deletes the original folder.
     * 
     * @param string fileZipLocation The location where the zip file will be created or overwritten.
     * @param string folderPath The folderPath parameter is the path to the folder that you want to zip. It
     * should be a string representing the directory path on your file system.
     * @param string zipPassword The `zipPassword` parameter is a string that represents the password to be
     * used for encrypting the ZIP archive. This password will be required to extract the files from the
     * archive.
     * @param bool keepOriginal A boolean flag indicating whether to keep the original folder after zipping
     * or not. If set to true, the original folder will be deleted after the zip operation is completed. If
     * set to false, the original folder will be retained.
     * @param string encryption The "encryption" parameter is used to specify the encryption algorithm to
     * be used for encrypting the files in the ZIP archive. The default value is "EM_AES_256", which stands
     * for AES-256 encryption. This means that the files in the archive will be encrypted using the AES-256
     * encryption
     * 
     * @return array an array with the following keys:
     */
    public function zip(string $fileZipLocation, string $folderPath, string $zipPassword, bool $keepOriginal = false, string $encryption = "EM_AES_256"): array {
        // Create a new zip archive
        $zip = new \ZipArchive;


        if (is_dir(dirname($fileZipLocation)) === false) {
            mkdir(dirname($fileZipLocation), 0777, true);
        }
        // Open the zip archive
        if ($zip->open($fileZipLocation, \ZipArchive::CREATE) === TRUE) {
            try {
                if (is_dir($folderPath)) {
                    // Add files from the specified folder to the ZIP archive
                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath));
                    foreach ($files as $name => $file) {
                        // Skip directories
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($filePath) + 1);
                            $zip->addFile($filePath, $relativePath);
                            // Set the password for the archive
                            $zip->setPassword($zipPassword);
                            // This part will set that fille be encrypted with config password
                            $zip->setEncryptionName(basename($filePath), constant("\ZipArchive::{$encryption}"));
                        }
                    }
                } else {
                    $zip->addFile($folderPath, basename($folderPath));
                    // Set the password for the archive
                    $zip->setPassword($zipPassword);
                    // This part will set that fille be encrypted with config password
                    $zip->setEncryptionName(basename($folderPath), constant("\ZipArchive::{$encryption}"));
                }
                // Close the archive
                $zip->close();

                // remove the original folder
                if (!$keepOriginal) {
                    if (is_dir($folderPath)) self::deleteDir($folderPath);
                    if (is_file($folderPath)) unlink($folderPath);
                }

                return [
                    'success' => true,
                    'message' => "File Compressed Successfully.",
                    'file_name' => $fileZipLocation,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'file_name' => null,
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "Couldn't open the directory, Probably permission denied.",
                'file_name' => null,
                'target_location' => $fileZipLocation,
            ];
        }
    }

    /**
     * The function `deleteDir` recursively deletes a directory and all its contents.
     * 
     * @param string $dirPath The `dirPath` parameter is the path to the directory that you want to delete.
     */
    private function deleteDir($dirPath) {
        $it = new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator(
            $it,
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dirPath);
    }
}
