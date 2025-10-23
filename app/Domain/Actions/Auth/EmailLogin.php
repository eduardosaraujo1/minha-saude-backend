<?php

namespace App\Domain\Actions\Auth;

use App\Data\Models\User;
use App\Data\Services\Cache\CacheService;
use App\Data\Services\Cache\DTO\RegisterTokenEntry;
use App\Domain\Actions\Auth\DTO\LoginResult;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Constants;
use App\Utils\Result;
use Str;

class EmailLogin
{
    public function __construct(public CacheService $cacheService) {}

    /**
     * @return Result<LoginResult,\Exception>
     */
    public function execute(string $email, string $code): Result
    {
        try {
            $result = $this->cacheService->getEmailAuthCode($email);

            // Guard: ensure code is correct
            if ($result == null) {
                return Result::failure(new \Exception(ExceptionDictionary::EMAIL_NOT_FOUND));
            }

            if ($result !== $code) {
                return Result::failure(new \Exception(ExceptionDictionary::INCORRECT_AUTH_CODE));
            }

            // Authenticate: if user exists with this email, log them in; otherwise, return unregistered status
            $user = User::where('email', $email)->first();
            assert($user instanceof User || $user === null); // intelissense helper

            if ($user) {
                $sessionToken = $user->createToken(Constants::DEFAULT_SANCTUM_TOKEN_NAME);
                $this->cacheService->clearEmailAuthCode($email);

                return Result::success(LoginResult::successful(
                    $sessionToken->plainTextToken
                ));
            }

            // Unregistered user: create register token and return
            $registerToken = "$email-".Str::random(32);
            $this->cacheService->putRegisterToken(new RegisterTokenEntry(
                token: $registerToken,
                email: $email,
                googleId: null,
                ttl: now()->addMinutes(15)
            ));

            return Result::success(LoginResult::needsRegistration($registerToken));
        } catch (\Exception $th) {
            return Result::failure($th);
        }
    }
}
