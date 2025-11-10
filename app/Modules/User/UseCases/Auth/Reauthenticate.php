<?php

namespace App\Modules\User\UseCases\Auth;

use App\Http\Exceptions\ApiException;
use App\Modules\User\DTOs\Auth\ReauthenticateFormData;
use App\Modules\User\DTOs\Auth\ReauthenticateResult;
use App\Modules\User\Models\User;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Modules\User\Services\Ports\GoogleServicePort;
use App\Utils\Result;
use Str;

/**
 * This action handles reauthentication and returns a temporary reauth token
 */
class Reauthenticate
{
    public function __construct(
        private GoogleServicePort $googleService,
        private CacheServicePort $cacheService
    ) {}

    /**
     * Executes the Action
     *
     * @return Result<ReauthenticateResult, ApiException>
     */
    public function execute(ReauthenticateFormData $data): Result
    {
        try {
            $user = null;

            // Verify credentials based on auth type
            if ($data->authType === 'google' && $data->googleAuth !== null) {
                $user = $this->verifyGoogleAuth($data->googleAuth->oauthToken);
            } elseif ($data->authType === 'email' && $data->emailAuth !== null) {
                $user = $this->verifyEmailAuth($data->emailAuth->email, $data->emailAuth->code);
            }

            if ($user === null) {
                return Result::failure(ApiException::emailNotFound());
            }

            // Generate reauth token
            $token = 'reauth-'.Str::random(32);
            $this->cacheService->putReauthenticateToken(
                (string) $user->id,
                $token,
                now()->addMinutes(15)
            );

            return Result::success(new ReauthenticateResult($token));
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }

    private function verifyGoogleAuth(string $oauthToken): ?User
    {
        $exchangeResult = $this->googleService->getUserInfo($oauthToken);

        if ($exchangeResult->isFailure()) {
            return null;
        }

        $userInfo = $exchangeResult->getOrThrow();

        return User::where('google_id', $userInfo->googleId)->first();
    }

    private function verifyEmailAuth(string $email, string $code): ?User
    {
        $cachedCode = $this->cacheService->getEmailAuthCode($email);

        if ($cachedCode === null || $cachedCode !== $code) {
            return null;
        }

        return User::where('email', $email)->first();
    }
}
