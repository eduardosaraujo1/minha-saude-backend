<?php

namespace App\Data\Services\Google;

use App\Data\DTO\GoogleUserInfo;

interface IGoogleService
{
    public function getUserInfo(string $oauthToken): GoogleUserInfo;
}
