<?php

namespace App\Domain\Actions\Auth\DTO;

use App\Data\Models\User;

class RegisterResult
{
    public function __construct(
        public string $sessionToken,
        public User $user,
    ) {}

    public function toArray(): array
    {
        return [
            'sessionToken' => $this->sessionToken,
            'user' => $this->user,
        ];
    }
}
