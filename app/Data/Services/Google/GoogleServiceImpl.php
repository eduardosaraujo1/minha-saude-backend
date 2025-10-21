<?php

namespace App\Data\Services\Google;

use App\Data\Services\Google\DTO\UserInfo;
use App\Utils\Result;
use Laravel\Socialite\Facades\Socialite;

class GoogleServiceImpl implements GoogleService
{
    public function getUserInfo(string $oauthToken): Result
    {
        try {
            // phpcs:ignore
            $driver = Socialite::driver('google')->stateless();

            // Improve intelissense
            assert($driver instanceof \Laravel\Socialite\Two\GoogleProvider);

            // Android server auth codes (from authorizeServer()) don't use a redirect_uri
            // Pass empty string to ensure Socialite omits the redirect_uri parameter
            $tokenResponse = $driver->redirectUrl('')->getAccessTokenResponse($oauthToken);

            $accessToken = $tokenResponse['access_token'] ?? null;

            // If unsuccessful return Failure
            if (! $accessToken) {
                return Result::failure(new \Exception('Access token not found in token response'));
            }

            // Then use the access token to get user info
            $user = $driver->userFromToken($accessToken);

            // If unsuccessful return Failure
            if (! $user) {
                return Result::failure(new \Exception('Failed to retrieve user info'));
            }

            // Return UserInfo with retrieved data
            return Result::success(new UserInfo(
                googleId: $user->id,
                email: $user->email,
            ));
        } catch (\Exception $e) {
            return Result::failure($e);
        }
    }
}
