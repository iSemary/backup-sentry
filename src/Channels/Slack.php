<?php

namespace iSemary\BackupSentry\Channels;

class Slack {
    private $webhookURL;

    public function __construct($config) {
        $this->webhookURL = $config->channels->slack->WebhookURL;
    }

    /**
     * The function sends a message to a Slack channel using a webhook URL.
     * 
     * @param message The message parameter is the text message that you want to send to Slack. It can be
     * any string value that you want to notify or alert others about.
     * @param username The username parameter is used to specify the name that will be displayed as the
     * sender of the message in Slack. By default, it is set to 'BackupSentry | Alert'.
     * @param emoji The `emoji` parameter is used to specify the emoji icon that will be displayed next to
     * the message in Slack. By default, it is set to `:robot_face:`, which represents a robot face emoji.
     * However, you can change it to any other valid emoji code to customize the icon.
     * 
     * @return array with the following keys:
     */
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
