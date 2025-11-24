<?php

namespace App\Modules\User\Logic;

use App\Modules\User\DTOs\Cache\RegisterTokenEntry;
use App\Modules\User\Services\Ports\CacheServicePort;
use Str;

class GenerateRegisterToken
{
    public function __construct(public CacheServicePort $cacheService) {}

    // Logic for generating register tokens would go here
    public function execute(string $email, ?string $googleId): RegisterTokenEntry
    {
        $registerTokenEntry = new RegisterTokenEntry(
            token: "$email-".Str::random(32),
            email: $email,
            googleId: $googleId,
            ttl: now()->addMinutes(15)
        );
        $this->cacheService->putRegisterToken($registerTokenEntry);

        return $registerTokenEntry;
    }
}
