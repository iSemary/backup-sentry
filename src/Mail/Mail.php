<?php

namespace iSemary\BackupSentry;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {

    private $config;
    private $mailer;

    public function __construct() {
        $this->config = new Config;
        $this->mailer = new PHPMailer;
        $this->mailer->isSMTP();
        // Configure SMTP settings here
        $this->mailer->Host = $this->config->env->get("MAIL_HOST");
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config->env->get("MAIL_HOST");
        $this->mailer->Password = $this->config->env->get("MAIL_HOST");
        $this->mailer->SMTPSecure = $this->config->env->get("MAIL_HOST");
        $this->mailer->Port = $this->config->env->get("MAIL_PORT");
        $this->mailer->setFrom($this->config->env->get("MAIL_FROM_NAME"), $this->config->env->get("MAIL_FROM_NAME"));
    }

    public function send($to, $subject, $message) {
        $this->mailer->addAddress($to);

        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;

        if ($this->mailer->send()) {
            return true;
        }
        return false;
    }
}
