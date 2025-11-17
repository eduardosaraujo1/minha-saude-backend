<?php

namespace App\Modules\User\UseCases\Auth;

use App\Http\Exceptions\ApiException;
use App\Modules\User\DTOs\Auth\LoginResult;
use App\Modules\User\DTOs\Cache\RegisterTokenEntry;
use App\Modules\User\Models\User;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Modules\User\Services\Ports\GoogleServicePort;
use App\Modules\User\UserModule;
use App\Utils\Result;
use Illuminate\Support\Facades\App;
use Str;

/**
 * This Action handles login via Google OAuth token and returns the appropriate LoginResult
 */
class GoogleLogin
{
    public function __construct(
        private GoogleServicePort $googleService,
        private CacheServicePort $cacheService
    ) {}

    /**
     * Executes the Action
     *
     * @return Result<LoginResult, ApiException>
     */
    public function execute(string $oauthToken): Result
    {
        try {
            if (App::environment() !== "production" && $oauthToken === "fake_server_auth_code") {
                // Simulate a successful response for the fake token
                $exchangeResult = Result::success((object)[
                    'googleId' => '1234567898642',
                    'email' => 'tccminhasaude2025@gmail.com'
                ]);
            } else {
                // Use GoogleService to get e-mail and Google ID from the OAuth token
                $exchangeResult = $this->googleService->getUserInfo($oauthToken);
            }

            if ($exchangeResult->isFailure()) {
                return Result::failure(ApiException::invalidOauthToken());
            }

            $userInfo = $exchangeResult->getOrThrow();
            $googleId = $userInfo->googleId;
            $email = $userInfo->email;

            // If google id exists in the database, retrieve the user, generate token, and return LoginResult
            $user = User::where('google_id', $googleId)->first();

            if ($user !== null) {
                // User exists, generate session token
                $sessionToken = $user->createToken(UserModule::DEFAULT_SANCTUM_TOKEN_NAME)->plainTextToken;

                return Result::success(LoginResult::successful(
                    token: $sessionToken,
                ));
            }

            // If user does not exist, generate a register token, store in cache, and return LoginResult indicating not registered
            $token = "$googleId-".Str::random(32);
            $this->cacheService->putRegisterToken(new RegisterTokenEntry(
                token: $token,
                googleId: $googleId,
                email: $email,
                ttl: now()->addMinutes(15)
            ));

            return Result::success(LoginResult::needsRegistration(
                token: $token
            ));
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
