<?php

namespace App\Data\Services\Mailer;

interface Mailer
{
    public function sendVerificationCodeEmail(string $email, string $code);
}
