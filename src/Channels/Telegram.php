<?php

namespace iSemary\BackupSentry\Channels;


class Telegram {
    private $botToken;

    public function __construct($config) {
        $this->botToken = $config->channels->telegram->botToken;
    }
    /**
     * The function sends a message to a Telegram chat using the Telegram Bot API.
     * 
     * @param chatID The chatID parameter is the unique identifier for the chat or conversation you want to
     * send the message to. It can be a user's ID, a group chat ID, or a channel ID.
     * @param message The "message" parameter is the text message that you want to send to the specified
     * chat ID. It can be any string or message that you want to send to the user or group on Telegram.
     * 
     * @return array with the following keys: status, success, message, response
     */
    public function send($chatID, $message):array {
        $url = "https://api.telegram.org/bot" . $this->botToken . "/sendMessage";
        $data = array(
            'chat_id' => $chatID,
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
                'response' => []
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
