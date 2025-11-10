<?php

namespace App\Modules\User\DTOs\Auth;

readonly class ReauthenticateResult
{
    public function __construct(
        public string $reauthToken
    ) {}

    public function toArray(): array
    {
        return [
            'reauthToken' => $this->reauthToken,
        ];
    }
}
