<?php

namespace App\Data\Services\Mailer;

class SmtpMailer implements Mailer
{
    public function sendVerificationCodeEmail(string $email, string $code)
    {
        // Implementation for sending verification code email via SMTP
    }
}
