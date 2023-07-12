<?php

namespace iSemary\BackupSentry\Cloud;

use iSemary\BackupSentry\Config;

class AWS {
    private string $config;
    private $accessKey;
    private $secretKey;
    private $bucketName;

    public function __construct() {
        $this->config = new Config;

        $this->accessKey = $this->config->accessKey;
        $this->secretKey = $this->config->secretKey;
        $this->bucketName = $this->config->bucketName;
    }

    public function upload($filePath) {
        $keyName = basename($filePath);
        $url = "https://{$this->bucketName}.s3.amazonaws.com/{$keyName}";

        // Read the file content
        $fileContent = file_get_contents($filePath);

        // Generate the necessary headers
        $date = gmdate('D, d M Y H:i:s T');
        $signature = base64_encode(hash_hmac('sha1', "PUT\n\n\n$date\n/$this->bucketName/$keyName", $this->secretKey, true));
        $headers = [
            "Date: $date",
            "Authorization: AWS $this->accessKey:$signature",
            "Content-Type: application/octet-stream",
            "Content-Length: " . strlen($fileContent),
        ];

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);

        // Execute the request
        $response = curl_exec($ch);

        // Check the response
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL
        curl_close($ch);
        if ($statusCode === 200) {
            return ["status" => 200, "success" => true, "message" => "File uploaded successfully to S3 bucket."];
        } else {
            return ["status" => 400, "success" => false, "message" => "Error uploading file to S3 bucket. Status code: $statusCode"];
        }
    }
}
