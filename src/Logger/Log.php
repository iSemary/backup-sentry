<?php

namespace iSemary\BackupSentry\Logger;

class Log {

    /**
     * The function writes a message to a file, creating the file and its directory if they don't exist,
     * and returns a success or failure message.
     * 
     * @param message The message parameter is the content that you want to write to the file. It can be
     * any string or data that you want to log or save to the file.
     * @param file The `file` parameter is the path to the file where the message will be logged.
     * 
     * @return array with the following keys and values:
     */
    public function write($message, $file):array {
        try {

            if (!is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }

            if (!file_exists($file)) {
                fopen($file, "w");
            }

            $file = fopen($file, 'a');

            $message = self::formatLogMessage($message);
            fwrite($file, $message);

            fclose($file);

            return [
                'status' => 200,
                'success' => true,
                'message' => "Message logged successfully"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'success' => false,
                'message' => "Failure on logging message : " . $e->getMessage()
            ];
        }
    }
    /**
     * The function "formatLogMessage" formats a log message by adding a timestamp and converting the
     * original message to a JSON string.
     * 
     * @param originalMessage The original message is a variable that contains the message you want to
     * format for logging purposes.
     * 
     * @return string $message formatted log message.
     */
    private function formatLogMessage($originalMessage):string {
        $dateTime = "[" . date("Y-m-d H:i:s") . "]";
        $message = "------------------------------------$dateTime-----------------------------------------------\n";
        $message .= json_encode($originalMessage);
        $message .= "\n";
        return $message;
    }
}
