<?php

namespace App\Data\Services\Google;

use App\Data\Services\Google\DTO\UserInfo;
use App\Utils\Result;

interface GoogleService
{
    /**
     * Exchanges OAuth Server Token for Google User Info
     *
     * For more information on OAuth2 Google Server Tokens, see https://developers.google.com/identity/protocols/oauth2/web-server
     *
     * Also, try it out on https://developers.google.com/oauthplayground/
     *
     * @return Result<UserInfo, \Exception>
     */
    public function getUserInfo(string $oauthToken): Result;
}
