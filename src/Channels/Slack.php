<?php

namespace iSemary\BackupSentry\Channels;

class Slack {
    private $webhookURL;

    public function __construct($config) {
        $this->webhookURL = $config->channels->slack->slackWebhookURL;
    }


    public function send($message, $username = 'BackupSentry | Alert', $emoji = ':robot_face:') {
        $data = array(
            'username' => $username,
            'text' => $message,
            'icon_emoji' => $emoji
        );

        $jsonData = json_encode($data);

        $ch = curl_init($this->webhookURL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP status code
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return [
                'status' => 200,
                'success' => true,
                'message' => "Slack alert sent successfully.",
            ];
        } else {
            return [
                'status' => 400,
                'success' => false,
                'message' => "Failure on sending slack alert.",
                'response' => $response
            ];
        }
    }
}
