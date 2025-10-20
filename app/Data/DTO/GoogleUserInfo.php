<?php

namespace App\Data\DTO;

class GoogleUserInfo extends DTO
{
    public function __construct(
        public string $googleId,
        public string $email,
    ) {}
}
