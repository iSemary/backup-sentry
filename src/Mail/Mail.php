<?php

namespace iSemary\BackupSentry\Mail;

use PHPMailer\PHPMailer\PHPMailer;

class Mail {

    private $config;
    private $mailer;
    private $successSubject;
    private $failureSubject;
    private $dateTimeFormatted;

    public function __construct($config) {
        $this->config = $config;
        $this->mailer = new PHPMailer;
        $this->dateTimeFormatted = date('d F Y');
        $this->successSubject = "Successful Backup " . $this->dateTimeFormatted;
        $this->failureSubject = "Failure Backup " . $this->dateTimeFormatted;

        if ($this->config->env->get("MAIL_DRIVER") == "smtp") $this->mailer->isSMTP();

        // $this->mailer->SMTPDebug = 2;
        $this->mailer->isHTML(true);
        $this->mailer->Host = $this->config->env->get("MAIL_HOST");
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config->env->get("MAIL_USERNAME");
        $this->mailer->Password = $this->config->env->get("MAIL_PASSWORD");
        $this->mailer->SMTPSecure = $this->config->env->get("MAIL_ENCRYPTION");
        $this->mailer->Port = $this->config->env->get("MAIL_PORT");
        $this->mailer->setFrom($this->config->env->get("MAIL_FROM_ADDRESS"), $this->config->env->get("MAIL_FROM_NAME"));
    }

    public function send($status, $content) {
        // collect all receivers emails
        $toAddresses = $this->config->configFile['backup']['mail']['to'];
        foreach ($toAddresses as $toAddress) {
            $this->mailer->addAddress($toAddress);
        }
        // collect all CC based on the status of the backup
        if ($this->config->configFile['backup']['email_alert']) {
            $toCCs = ($status ? $this->config->configFile['options']['alert_successful_backup_email_to'] : $this->config->configFile['options']['alert_failure_backup_email_to']);
            foreach ($toCCs as $toCC) {
                $this->mailer->addCC($toCC);
            }
        }
        // set the email subject based on the status of the backup
        $this->mailer->Subject = $status ? $this->successSubject : $this->failureSubject;
        // get mail body
        $this->mailer->Body = self::getMailBody($status, $content);
        // try sending the email
        try {
            $this->mailer->send();
            return [
                'status' => 200,
                'success' => true,
                'message' => "Mail sent successfully"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'success' => false,
                'message' => "Failed to send mail",
                'response' =>  $e->getMessage()
            ];
        }
    }

    private function getMailBody($status, $content) {
        return ($status ? self::returnSuccessMail($content) : self::returnFailureMail($content));
    }

    private function returnSuccessMail($content) {
        $output = '';
        $output .= "<h3 class='text-center'>Successful Backup</h3>";
        $output .= "<p class='text-center'>Your {$this->dateTimeFormatted} Backup is successfully created!</p>";
        // AWS link or google drive link and complete zip file name
        return $output;
    }
    private function returnFailureMail($content) {
        $content = json_encode($content);
        $output = '';
        $output .= "<h3 class='text-center'>Failure Backup</h3>";
        $output .= "<p class='text-center'>Your {$this->dateTimeFormatted} Backup has an error and couldn't be uploaded!</p>";
        $output .= "<pre>{$content}</pre>";
        return $output;
    }
}
