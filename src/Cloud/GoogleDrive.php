<?php

namespace iSemary\BackupSentry\Cloud;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;


class GoogleDrive {
    private $config;
    private string $uploadEndPoint;
    private string $authUrl;
    private array $fileMetaData;
    private string $accessToken;
    private string $googleDriveRefreshToken;
    private string $googleDriveClientID;
    private string $googleDriveFolderID;
    private string $googleDriveClientSecret;
    private string $googleDriveConfigPath;

    public function __construct($config) {
        $this->config = $config;
        $this->uploadEndPoint = "https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart";
        $this->authUrl = "https://oauth2.googleapis.com/token";
        $this->googleDriveClientID = $this->config->cloud['google_drive']['client_id'];
        $this->googleDriveFolderID = $this->config->cloud['google_drive']['folder_id'];
        $this->googleDriveClientSecret = $this->config->cloud['google_drive']['client_secret'];
        $this->googleDriveRefreshToken = $this->config->cloud['google_drive']['refresh_token'];
        $this->googleDriveConfigPath = $this->config->projectPath . '/storage/backup-sentry/config.json';
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

        $responseBody = $res->getBody()->getContents();
        $responseBody = json_decode($responseBody, true);

        if (isset($responseBody['id'])) {
            $response = [
                'success' => true,
                'status' => 200,
                'message' => "File uploaded to google drive successfully."
            ];
        } else {
            $response = [
                'success' => false,
                'status' => 400,
                'message' => "Failure on uploading file to google drive.",
                "response" => json_encode($res)
            ];
        }

        return $response;
    }
}
