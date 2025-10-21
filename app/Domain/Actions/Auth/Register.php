<?php

namespace App\Domain\Actions\Auth;

use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use App\Data\Services\Cache\CacheService;
use App\Domain\Actions\Auth\DTO\RegisterFormData;
use App\Domain\Actions\Auth\DTO\RegisterResult;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Result;

/**
 * Registers a new user and authenticates them
 */
class Register
{
    public function __construct(private CacheService $cacheService) {}

    /**
     * Executes the Action
     *
     * @return Result<RegisterResult, \Exception> session token on success
     */
    public function execute(RegisterFormData $userData): Result
    {
        try {
            // Checks register token in cache to determine user e-mail and google ID
            $tokenEntry = $this->cacheService->getRegisterTokenData($userData->registerToken);

            // Cache must be an array with an e-mail field
            if (! $tokenEntry) {
                return Result::failure(new \Exception(ExceptionDictionary::INVALID_REGISTER_TOKEN));
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
            assert($user instanceof User); // intelissense helper

            // Create a session token for the new user
            $token = $user->createToken('session-token')->plainTextToken;

            return Result::success(new RegisterResult(sessionToken: $token, user: $user));
        } catch (\Exception $e) {
            return Result::failure($e);
        }
    }
}
