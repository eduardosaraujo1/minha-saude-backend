<?php

namespace App\Domain\Actions;

use App\Data\Models\User;
use App\Data\Services\Google\GoogleService;
use App\Domain\Actions\DTO\LoginResult;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Result;
use Cache;
use Str;

/**
 * This Action handles login via Google OAuth token and returns the appropriate LoginResult
 */
class GoogleLogin
{
    public function __construct(private GoogleService $googleService) {}

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

            $googleUserInfo = $exchangeResult->getOrThrow();
            $googleId = $googleUserInfo->googleId;
            $email = $googleUserInfo->email;

            // If google id exists in the database, retrieve the user, generate token, and return LoginResult
            $user = User::where('google_id', $googleId)->first();
            assert($user instanceof User || $user === null);

            if ($user !== null) {
                // User exists, generate session token
                $sessionToken = $user->createToken('session-token')->plainTextToken;

                return Result::success(LoginResult::successful(
                    token: $sessionToken,
                ));
            }

            // If user does not exist, generate a register token, store in cache, and return LoginResult indicating not registered
            $token = "register-$googleId-".Str::random(32);
            Cache::put(
                key: $token,
                value: ['google_id' => $googleId, 'email' => $email],
                ttl: now()->addMinutes(15)
            );

            return Result::success(LoginResult::needsRegistration(
                token: $token
            ));
        } catch (\Exception $e) {
            return Result::failure($e);
        }
    }
}
