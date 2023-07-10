<?php

namespace iSemary\BackupSentry;

class Compress {

    public function zip(string $fileZipLocation, string $filePath, string $zipPassword): array {
        // Create a new zip archive
        $zip = new \ZipArchive;

        // Open the zip archive
        if ($zip->open($fileZipLocation, \ZipArchive::CREATE) === TRUE) {

            try {
                // Add the file to the archive
                $zip->addFile($filePath, basename($filePath));

                // Set the password for the archive
                // Set global (for each file) password
                $zip->setPassword($zipPassword);

                // This part will set that 'data.txt' will be encrypted with your password
                $zip->setEncryptionName(basename($filePath), \ZipArchive::EM_AES_256);

                // Close the archive
                $zip->close();


                if (file_exists($filePath)) {
                    unlink($filePath);
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
                'message' => "Could'\nt open the directory, Probably permission denied.",
                'file_name' => null,
            ];
        }
    }
}
