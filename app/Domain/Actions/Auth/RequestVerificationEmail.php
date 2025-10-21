<?php

namespace App\Domain\Actions\Auth;

use App\Mail\AuthVerificationCode;
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
            $code = (string) rand(100000, 999999);

            Mail::to($email)->send(new AuthVerificationCode($code));

            return Result::success(null);
        } catch (\Exception $th) {
            return Result::failure($th);
        }
    }
}
