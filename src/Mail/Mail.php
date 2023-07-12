<?php

namespace iSemary\BackupSentry\Mail;

use iSemary\BackupSentry\Config;
use PHPMailer\PHPMailer\PHPMailer;

class Mail {

    private $config;
    private $mailer;

    public function __construct() {
        $this->config = new Config;
        $this->mailer = new PHPMailer;

        if ($this->config->env->get("MAIL_DRIVER") == "smtp") $this->mailer->isSMTP();

        // $this->mailer->SMTPDebug = 2;
        $this->mailer->Host = $this->config->env->get("MAIL_HOST");
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config->env->get("MAIL_USERNAME");
        $this->mailer->Password = $this->config->env->get("MAIL_PASSWORD");
        $this->mailer->SMTPSecure = $this->config->env->get("MAIL_ENCRYPTION");
        $this->mailer->Port = $this->config->env->get("MAIL_PORT");
        $this->mailer->setFrom($this->config->env->get("MAIL_FROM_ADDRESS"), $this->config->env->get("MAIL_FROM_NAME"));
    }

    public function send($status, $to, $subject, $message) {
        $this->mailer->addAddress($to);

        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;


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
                'message' => "Failed to send mail : " . $e->getMessage(),
            ];
        }
    }
}
