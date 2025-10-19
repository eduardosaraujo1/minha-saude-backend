<?php

namespace App\Data\Services\Google;

use App\Data\DTO\GoogleUserInfo;

class GoogleService implements IGoogleService
{
    public function getUserInfo(string $oauthToken): GoogleUserInfo
    {
        // Use Socialite or API to get info from token

        // If unsuccessful return Failure

        // Return GoogleUserInfo with retrieved data
    }
}
