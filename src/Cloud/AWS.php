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
