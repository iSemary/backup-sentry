<?php

namespace iSemary\BackupSentry\Cloud;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use iSemary\BackupSentry\Config;


class GoogleDrive {
    private string $config;
    private string $uploadEndPoint;
    private string $googleDriveFolderID;
    private string $googleDriveClientID;
    private string $googleDriveClientSecret;
    private string $googleDriveRefreshToken;
    private string $googleDriveConfigPath;
    private string $authUrl;
    private string $accessToken;
    private array $fileMetaData;
    private array $params;

    private $file;


    public function __construct() {
        $this->config = new Config;
        $this->uploadEndPoint = "https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart";
        $this->authUrl = "https://oauth2.googleapis.com/token";
        $this->googleDriveClientID = $this->config->googleDriveClientID;
        $this->googleDriveClientSecret = $this->config->googleDriveClientSecret;
        $this->googleDriveRefreshToken = $this->config->googleDriveRefreshToken;
        $this->googleDriveConfigPath = $this->config->projectPath . '/config/config.json';
    }


    public function getGoogleDriveAccessToken() {
        $data = array(
            "client_id" => $this->googleDriveClientID,
            "client_secret" => $this->googleDriveClientSecret,
            "refresh_token" => $this->googleDriveRefreshToken,
            "grant_type" => "refresh_token"
        );


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->authUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ));
        $response = curl_exec($curl);
        curl_close($curl);


        $response = json_decode($response, true);

        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
        }
    }

    public function upload($filePath) {

        $this->fileMetaData = [
            'name' => basename($filePath),
            'parents' => [$this->googleDriveFolderID],
        ];

        $this->getGoogleDriveAccessToken();

        $client = new Client();

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        $content = file_put_contents($this->googleDriveConfigPath, json_encode($this->fileMetaData));
        $options = [
            'multipart' => [
                [
                    'name' => '',
                    'contents' =>  Utils::tryFopen($this->googleDriveConfigPath, 'r'),
                ],
                [
                    'name' => '',
                    'contents' => Utils::tryFopen($filePath, 'r'),
                ]
            ]
        ];

        $request = new Request('POST', $this->uploadEndPoint, $headers);
        $res = $client->send($request, $options);

        return $res;
    }
}
