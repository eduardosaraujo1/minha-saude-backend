<?php

namespace App\Modules\User\Services\Ports;

use App\Utils\Result;

interface GoogleServicePort
{
    /**
     * Exchanges OAuth Server Token for Google User Info
     *
     * For more information on OAuth2 Google Server Tokens, see https://developers.google.com/identity/protocols/oauth2/web-server
     *
     * Also, try it out on https://developers.google.com/oauthplayground/
     *
     * @return Result<\App\Modules\User\DTOs\Google\UserInfo, \Exception>
     */
    public function getUserInfo(string $oauthToken): Result;
}
