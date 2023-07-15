<?php

namespace iSemary\BackupSentry\Logger;

class Log {

    public function write($message, $file) {
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

    private function formatLogMessage($originalMessage) {
        $dateTime = "[" . date("Y-m-d H:i:s") . "]";
        $message = "------------------------------------$dateTime-----------------------------------------------\n";
        $message .= json_encode($originalMessage);
        $message .= "\n";
        return $message;
    }
}
