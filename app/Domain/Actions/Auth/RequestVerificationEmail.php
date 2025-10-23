<?php

namespace App\Domain\Actions\Auth;

use App\Domain\Mail\AuthVerificationCode;
use App\Utils\Result;
use Mail;

class RequestVerificationEmail
{
    /**
     * Sends the verification code e-mail to the provided path
     *
     * @return Result<null,\Exception>
     */
    public function execute(string $email): Result
    {
        try {
            $code = $this->generateCode();

            Mail::to($email)->send(new AuthVerificationCode($code));

            return Result::success(null);
        } catch (\Exception $th) {
            return Result::failure($th);
        }
    }

    private function generateCode(): string
    {
        $min = 100000;
        $max = 999999;

        return (string) random_int($min, $max);
    }
}
