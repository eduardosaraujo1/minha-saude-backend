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
        return app(UseCases\Auth\EmailLogin::class)->execute($email, $code);
    }

    /**
     * Login user via google
     *
     * @return Result<DTOs\Auth\LoginResult, \App\Http\Exceptions\ApiException>
     */
    public function googleLogin(string $oauthToken): Result
    {
        return app(UseCases\Auth\GoogleLogin::class)->execute($oauthToken);
    }

    /**
     * Logout user
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function logout(): Result
    {
        return app(UseCases\Auth\Logout::class)->execute();
    }

    /**
     * Register new user
     *
     * @return Result<DTOs\Auth\RegisterResult, \App\Http\Exceptions\ApiException>
     */
    public function register(RegisterFormData $userData): Result
    {
        return app(UseCases\Auth\Register::class)->execute($userData);
    }

    /**
     * Request email verification code
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function requestVerificationEmail(string $email): Result
    {
        return app(UseCases\Auth\RequestVerificationEmail::class)->execute($email);
    }

    /**
     * Reauthenticate user
     *
     * @return Result<DTOs\Auth\ReauthenticateResult, \App\Http\Exceptions\ApiException>
     */
    public function reauthenticate(DTOs\Auth\ReauthenticateFormData $data): Result
    {
        return app(UseCases\Auth\Reauthenticate::class)->execute($data);
    }

    /**
     * Get current user information
     *
     * @return Result<DTOs\Profile\ProfileDto, \App\Http\Exceptions\ApiException>
     */
    public function getUserInfo(): Result
    {
        return app(UseCases\Profile\GetUserInfo::class)->execute();
    }

    /**
     * Update user's name
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function updateName(string $nome): Result
    {
        return app(UseCases\Profile\UpdateName::class)->execute($nome);
    }

    /**
     * Update user's birthdate
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function updateBirthdate(string $dataNascimento): Result
    {
        return app(UseCases\Profile\UpdateBirthdate::class)->execute($dataNascimento);
    }

    /**
     * Update user's phone
     *
     * @return Result<null, \App\Http\Exceptions\ApiException>
     */
    public function updatePhone(string $telefone): Result
    {
        return app(UseCases\Profile\UpdatePhone::class)->execute($telefone);
    }

    public function requestDeletion(string $reauthToken): Result
    {

        return app(UseCases\Profile\RequestDeletion::class)->execute($reauthToken);
    }
}
