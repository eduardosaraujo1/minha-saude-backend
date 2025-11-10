<?php

namespace App\Modules\User;

use App\Modules\User\DTOs\Auth\RegisterFormData;
use App\Utils\Result;

class UserModule
{
    public const DEFAULT_SANCTUM_TOKEN_NAME = 'session-token';

    /**
     * Login user via email and code.
     *
     * @return Result<DTOs\Auth\LoginResult, \App\Http\Exceptions\ApiException>
     */
    public function emailLogin(string $email, string $code): Result
    {
        return app(UseCases\EmailLogin::class)->execute($email, $code);
    }

    /**
     * Login user via google
     *
     * @return Result<DTOs\Auth\LoginResult, \App\Http\Exceptions\ApiException>
     */
    public function googleLogin(string $oauthToken): Result
    {
        return app(UseCases\GoogleLogin::class)->execute($oauthToken);
    }

    /**
     * Logout user
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function logout(): Result
    {
        return app(UseCases\Logout::class)->execute();
    }

    /**
     * Register new user
     *
     * @return Result<DTOs\Auth\RegisterResult, \App\Http\Exceptions\ApiException>
     */
    public function register(RegisterFormData $userData): Result
    {
        return app(UseCases\Register::class)->execute($userData);
    }

    /**
     * Request email verification code
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function requestVerificationEmail(string $email): Result
    {
        return app(UseCases\RequestVerificationEmail::class)->execute($email);
    }
}
