<?php

namespace iSemary\BackupSentry\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use iSemary\BackupSentry\Env\EnvHandler;


class GoogleDrive {
    private string $uploadEndPoint;
    private string $gClientID;
    private string $gClientSecret;
    private string $gRefreshToken;
    private string $authUrl;
    private string $accessToken;
    private array $fileMetaData;
    private array $params;

    private $file;


    public function __construct() {
        $env = new EnvHandler;
        $this->uploadEndPoint = "https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart";
        $this->authUrl = "https://oauth2.googleapis.com/token";
        $this->gClientID = $env->get("GOOGLE_DRIVE_CLIENT_ID");
        $this->gClientSecret = $env->get("GOOGLE_DRIVE_CLIENT_SECRET");
        $this->gRefreshToken = $env->get("GOOGLE_DRIVE_REFRESH_TOKEN");
    }


    public function getGoogleDriveAccessToken() {
        $data = array(
            "client_id" => $this->gClientID,
            "client_secret" => $this->gClientSecret,
            "refresh_token" => $this->gRefreshToken,
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

    public function upload($filePath, $fileName, $folderID) {

        $this->fileMetaData = [
            'name' => $fileName,
            'parents' => [$folderID],
        ];

        $this->getGoogleDriveAccessToken();

        $client = new Client();

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        $content = file_put_contents(storage_path('app/backup/db/config/config.json'), json_encode($this->fileMetaData));

        $options = [
            'multipart' => [
                [
                    'name' => '',
                    'contents' =>  Utils::tryFopen(storage_path('app/backup/db/config/config.json'), 'r'),
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
