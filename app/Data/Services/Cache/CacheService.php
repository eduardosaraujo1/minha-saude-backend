<?php

namespace App\Data\Services\Cache;

use App\Data\Services\Cache\DTO\RegisterTokenEntry;

interface CacheService
{
    /**
     * Puts a register token entry into the cache for future access
     *
     * Expires after $ttl time
     */
    public function putRegisterToken(RegisterTokenEntry $entry): void;

    /**
     * Gets the data associated with a register token stored in cache
     *
     * Returns null if no data is stored
     */
    public function getRegisterToken(string $registerToken): ?RegisterTokenEntry;

    /**
     * Removes the data associated with a register token stored in cache
     */
    public function clearRegisterToken(string $registerToken): void;
}
