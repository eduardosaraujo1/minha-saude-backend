<?php

namespace App\Domain\Actions\Auth\DTO;

class LoginResult
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

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
