<?php

namespace LunaCMS\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Exception;

class MailService
{
    private PHPMailer $mailer;

    public function __construct(array $config)
    {
        $this->mailer = new PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $config['host'] ?? 'localhost';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config['username'] ?? '';
            $this->mailer->Password = $config['password'] ?? '';
            $this->mailer->SMTPSecure = $config['encryption'] ?? 'tls';
            $this->mailer->Port = $config['port'] ?? 587;

            $this->mailer->setFrom($config['from_address'], $config['from_name']);
            if (!empty($config['reply_to_address'])) {
                $this->mailer->addReplyTo($config['reply_to_address'], $config['reply_to_name']);
            }

            $this->mailer->isHTML(true);
        } catch (PHPMailerException $e) {
            error_log('Mailer initialization failed: ' . $e->getMessage());
        }
    }

    public function sendEmail(string $toEmail, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);

            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);

            return $this->mailer->send();
        } catch (PHPMailerException $e) {
            error_log('Mailer Error: ' . $e->getMessage());
            return false;
        }
    }
}
