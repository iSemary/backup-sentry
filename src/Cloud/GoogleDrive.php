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


    /**
     * The function `getGoogleDriveAccessToken` retrieves an access token from Google Drive using the
     * provided client ID, client secret, and refresh token.
     */
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
    /**
     * The function `upload` uploads a file to Google Drive using the Google Drive API.
     * 
     * @param string filePath The `filePath` parameter is the path to the file that you want to upload to Google
     * Drive.
     * 
     * @return array with the following keys: 'success', 'status', 'message', and 'response'. The
     * 'success' key indicates whether the file was uploaded successfully or not. The 'status' key contains
     * the HTTP status code of the response. The 'message' key provides a message indicating the result of
     * the upload. The 'response' key contains the JSON-encoded response from the Google
     */
    public function upload($filePath):array {

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
