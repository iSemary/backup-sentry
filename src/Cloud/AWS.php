<?php

namespace iSemary\BackupSentry\Cloud;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class AWS {
    private $config;
    private $accessKey;
    private $secretKey;
    private $bucketName;
    private $region;

    public function __construct($config) {
        $this->config = $config;

        $this->accessKey = $this->config->cloud['aws']['access_key'];
        $this->secretKey = $this->config->cloud['aws']['secret_key'];
        $this->bucketName = $this->config->cloud['aws']['bucket_name'];
        $this->region = $this->config->cloud['aws']['region'];
    }
    /**
     * The function `upload` uploads a file to an S3 bucket using the AWS SDK for PHP.
     * 
     * @param filePath The filePath parameter is the path to the file that you want to upload to the S3
     * bucket. It should be a string representing the file's location on your local system.
     * 
     * @return array with the following keys:
     * - "status": The HTTP status code of the response.
     * - "success": A boolean indicating whether the file upload was successful or not.
     * - "message": A message describing the result of the file upload.
     * - "file_path": The stored file path in the S3 bucket, if the upload was successful.
     * - "response": The exception object
     */
    public function upload($filePath) {
        $s3 = new S3Client([
            'region'  => $this->region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $this->accessKey,
                'secret' => $this->secretKey,
            ]
        ]);
        try {
            $result = $s3->putObject([
                'Bucket' => $this->bucketName,
                'Key'    => basename($filePath),
                'SourceFile' => $filePath,
                "Content-Type: application/octet-stream",
            ]);
            $storedFilePath = $result['@metadata']['effectiveUri'];
            return ["status" => 200, "success" => true, "message" => "File uploaded successfully to S3 bucket.", "file_path" => $storedFilePath];
        } catch (AwsException $e) {
            return ["status" => 400, "success" => false, "message" => "Error uploading file to S3 bucket.", "response" => $e];
        }
    }
}
