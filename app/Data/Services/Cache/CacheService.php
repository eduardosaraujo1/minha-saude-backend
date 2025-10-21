<?php

namespace App\Data\Services\Cache;

use App\Data\Services\Cache\DTO\RegisterTokenEntry;
use DateTime;

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
    public function getRegisterTokenData(string $registerToken): ?RegisterTokenEntry;

    /**
     * Removes the data associated with a register token stored in cache
     */
    public function clearRegisterToken(string $registerToken): void;

    /**
     * Places the authentication code sent to the user by e-mail in cache
     *
     * Used for comparing the code provided by the user to the code sent by the server
     */
    public function putEmailAuthCode(string $email, string $code, ?DateTime $ttl): void;

    /**
     * Gets the authentication code sent to the user by e-mail in cache
     *
     * Used for comparing the code provided by the user to the code sent by the server
     */
    public function getEmailAuthCode(string $email): ?string;

    /**
     * Clears the authentication code sent to the user by e-mail in cache
     *
     * Used for comparing the code provided by the user to the code sent by the server
     */
    public function clearEmailAuthCode(string $email): void;
}
