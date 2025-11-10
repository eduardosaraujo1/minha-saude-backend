<?php

namespace App\Modules\User\UseCases\Auth;

use App\Http\Exceptions\ApiException;
use App\Modules\User\DTOs\Auth\RegisterFormData;
use App\Modules\User\DTOs\Auth\RegisterResult;
use App\Modules\User\DTOs\Auth\UserAuthMethod;
use App\Modules\User\Models\User;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Modules\User\UserModule;
use App\Utils\Result;

/**
 * Registers a new user and authenticates them
 */
class Register
{
    public function __construct(private CacheServicePort $cacheService) {}

    /**
     * Executes the Action
     *
     * @return Result<\App\Modules\User\DTOs\Auth\RegisterResult, ApiException> session token on success
     */
    public function execute(RegisterFormData $userData): Result
    {
        try {
            // Checks register token in cache to determine user e-mail and google ID
            $tokenEntry = $this->cacheService->getRegisterTokenData($userData->registerToken);

            // Cache must be an array with an e-mail field
            if (! $tokenEntry) {
                return Result::failure(ApiException::invalidRegisterToken());
            }

            // Create new user in the database with provided data
            $user = User::create([
                'name' => $userData->nome,
                'cpf' => $userData->cpf,
                'metodo_autenticacao' => $tokenEntry->isGoogle()
                    ? UserAuthMethod::Google
                    : UserAuthMethod::Email,
                'google_id' => $tokenEntry->googleId,
                'email' => $tokenEntry->email,
                'data_nascimento' => $userData->dataNascimento,
                'telefone' => $userData->telefone,
            ]);

            // Create a session token for the new user
            $token = $user->createToken(UserModule::DEFAULT_SANCTUM_TOKEN_NAME)->plainTextToken;

            return Result::success(new RegisterResult(
                sessionToken: $token,
                user: $user
            ));
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
