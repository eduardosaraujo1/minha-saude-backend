<?php

namespace App\Domain\DTO;

class LoginResult extends DTO
{
    public function __construct(
        public bool $isRegistered,
        public ?string $sessionToken = null,
        public ?string $registerToken = null
    ) {}

    public static function needsRegistration(string $token): self
    {
        return new self(isRegistered: false, registerToken: $token);
    }

    public static function successful(string $token): self
    {
        return new self(isRegistered: true, sessionToken: $token);
    }
}
