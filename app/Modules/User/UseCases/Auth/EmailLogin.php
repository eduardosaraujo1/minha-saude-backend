<?php

namespace App\Modules\User\UseCases\Auth;

use App\Http\Exceptions\ApiException;
use App\Modules\User\DTOs\Auth\LoginResult;
use App\Modules\User\DTOs\Cache\RegisterTokenEntry;
use App\Modules\User\Logic\GenerateRegisterToken;
use App\Modules\User\Models\User;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Modules\User\UserModule;
use App\Utils\Result;
use Str;

class EmailLogin
{
    public function __construct(
        public GenerateRegisterToken $generateRegisterToken,
        public CacheServicePort $cacheService
    ) {}

    /**
     * @return Result<LoginResult,ApiException>
     */
    public function execute(string $email, string $code): Result
    {
        try {
            $result = $this->cacheService->getEmailAuthCode($email);

            // Guard: ensure code is correct
            if ($result == null) {
                return Result::failure(ApiException::emailNotFound());
            }

            if ($result !== $code) {
                return Result::failure(ApiException::incorrectAuthCode());
            }

            // Authenticate: if user exists with this email, log them in; otherwise, return unregistered status
            $user = User::where('email', $email)->first();

            if ($user) {
                $sessionToken = $user->createToken(UserModule::DEFAULT_SANCTUM_TOKEN_NAME);
                $this->cacheService->clearEmailAuthCode($email);

                return Result::success(LoginResult::successful(
                    $sessionToken->plainTextToken
                ));
            }

            // Unregistered user: create register token and return
            $registerTokenEntry = new RegisterTokenEntry(
                token: "$email-".Str::random(32),
                email: $email,
                googleId: null,
                ttl: now()->addMinutes(15)
            );
            $this->cacheService->putRegisterToken($registerTokenEntry);

            return Result::success(LoginResult::needsRegistration($registerTokenEntry->token));
        } catch (\Exception $th) {
            return Result::failure(new ApiException($th->getMessage()));
        }
    }
}
