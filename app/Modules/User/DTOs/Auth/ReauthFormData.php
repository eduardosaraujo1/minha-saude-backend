<?php

namespace App\Modules\User\DTOs\Auth;

class ReauthFormData
{
    private function __construct(
        public ?string $googleOauthToken,
        public ?string $email,
        public ?string $code,
    ) {}
}
