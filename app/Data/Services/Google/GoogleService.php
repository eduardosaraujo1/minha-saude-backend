<?php

namespace App\Data\Services\Google;

use App\Data\DTO\GoogleUserInfo;
use App\Utils\Result;

interface GoogleService
{
    /**
     * It reads user information from Google using the provided OAuth token.
     *
     * @return Result<GoogleUserInfo, \Exception>
     */
    public function getUserInfo(string $oauthToken): Result;
}
