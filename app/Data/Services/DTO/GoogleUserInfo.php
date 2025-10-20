<?php

namespace App\Data\Services\DTO;

class GoogleUserInfo
{
    public function __construct(
        public string $googleId,
        public string $email,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
