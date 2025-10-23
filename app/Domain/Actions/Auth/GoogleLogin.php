<?php

namespace App\Domain\Actions\Auth;

use App\Data\Models\User;
use App\Data\Services\Cache\CacheService;
use App\Data\Services\Cache\DTO\RegisterTokenEntry;
use App\Data\Services\Google\GoogleService;
use App\Domain\Actions\Auth\DTO\LoginResult;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Constants;
use App\Utils\Result;
use Str;

/**
 * This Action handles login via Google OAuth token and returns the appropriate LoginResult
 */
class GoogleLogin
{
    public function __construct(
        private GoogleService $googleService,
        private CacheService $cacheService
    ) {}

    /**
     * Executes the Action
     *
     * @return Result<LoginResult, \Exception>
     */
    public function execute(string $oauthToken): Result
    {
        try {
            // Use GoogleService to get e-mail and Google ID from the OAuth token
            $exchangeResult = $this->googleService->getUserInfo($oauthToken);

            if ($exchangeResult->isFailure()) {
                return Result::failure(new \Exception(ExceptionDictionary::INVALID_OAUTH_TOKEN));
            }

            $userInfo = $exchangeResult->getOrThrow();
            $googleId = $userInfo->googleId;
            $email = $userInfo->email;

            // If google id exists in the database, retrieve the user, generate token, and return LoginResult
            $user = User::where('google_id', $googleId)->first();

            if ($user !== null) {
                // User exists, generate session token
                $sessionToken = $user->createToken(Constants::DEFAULT_SANCTUM_TOKEN_NAME)->plainTextToken;

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
            return Result::failure($e);
        }
    }
}
