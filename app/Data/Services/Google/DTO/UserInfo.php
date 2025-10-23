<?php

namespace App\Data\Services\Google\DTO;

class UserInfo
{
    public function __construct(
        public string $googleId,
        public string $email,
    ) {}

    public function toArray(): array
    {
        return [
            'googleId' => $this->googleId,
            'email' => $this->email,
        ];
    }
}
