<?php

namespace App\Data\Services\Cache\DTO;

class RegisterTokenEntry
{
    /**
     * @param  \DateInterval|\DateTimeInterface|int|null  $ttl
     */
    public function __construct(
        public string $token,
        public string $email,
        public ?string $googleId,
        public mixed $ttl
    ) {}

    public function isGoogle(): bool
    {
        return $this->googleId != null;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
