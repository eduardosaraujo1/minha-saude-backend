<?php

namespace App\Modules\User\Services\Ports;

use App\Modules\User\DTOs\Cache\RegisterTokenEntry;
use DateTime;

interface CacheServicePort
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

    /**
     * Stores a reauthenticate token in cache for temporary access verification
     *
     * @param  string  $userId  The user ID associated with this token
     * @param  string  $token  The reauthenticate token
     * @param  DateTime|null  $ttl  Time to live for the token (defaults to 15 minutes)
     */
    public function putReauthenticateToken(string $userId, string $token, ?DateTime $ttl): void;

    /**
     * Gets the user ID associated with a reauthenticate token
     *
     * @param  string  $token  The reauthenticate token
     * @return string|null The user ID or null if token not found/expired
     */
    public function getReauthenticateToken(string $token): ?string;

    /**
     * Clears a reauthenticate token from cache
     *
     * @param  string  $token  The reauthenticate token to remove
     */
    public function clearReauthenticateToken(string $token): void;
}
