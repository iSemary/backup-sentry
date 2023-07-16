<?php

namespace iSemary\BackupSentry\Channels;


class Telegram {
    private $botToken;

    public function __construct($config) {
        $this->botToken = $config->channels->telegram->botToken;
    }

    public function send($phone_number, $message) {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/sendMessage";
        $data = array(
            'chat_id' => $phone_number,
            'text' => $message,
        );

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        // Check if the message was sent successfully
        if ($result && $result['ok']) {
            return [
                'status' => 200,
                'success' => true,
                'message' => "Telegram alert sent successfully.",
            ];
        } else {
            return [
                'status' => 400,
                'success' => false,
                'message' => "Failure on sending telegram alert.",
                'response' => $result
            ];
        }
    }
}
