<?php

namespace App\Modules\User\UseCases\Auth;

use App\Http\Exceptions\ApiException;
use App\Modules\User\Mail\AuthVerificationCode;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Utils\Result;
use Mail;

class RequestVerificationEmail
{
    public function __construct(public CacheServicePort $cacheServicePort) {}

    /**
     * Sends the verification code e-mail to the provided path
     *
     * @return Result<null,ApiException>
     */
    public function execute(string $email): Result
    {
        try {
            $code = $this->generateCode();

            Mail::to($email)->send(new AuthVerificationCode($code));

            $this->cacheServicePort->putEmailAuthCode($email, $code, null);

            return Result::success(null);
        } catch (\Exception $th) {
            return Result::failure(new ApiException($th->getMessage()));
        }
    }

    private function generateCode(): string
    {
        $min = 100000;
        $max = 999999;

        return (string) random_int($min, $max);
    }
}
