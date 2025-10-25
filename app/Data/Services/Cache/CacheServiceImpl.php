<?php

namespace App\Data\Services\Cache;

use App\Data\Services\Cache\DTO\RegisterTokenEntry;
use Cache;
use DateTime;
use Log;

class CacheServiceImpl implements CacheService
{
    public function putRegisterToken(RegisterTokenEntry $entry): void
    {
        try {
            $token = $entry->token;
            Cache::put("register-$token", $entry, $entry->ttl);
        } catch (\Throwable $th) {
            Log::error('Error during cache storage: '.$th->getMessage(), [$th]);

            return;
        }
    }

    public function getRegisterTokenData(string $registerToken): ?RegisterTokenEntry
    {
        try {
            $entry = Cache::get("register-$registerToken");

            if (! $entry instanceof RegisterTokenEntry) {
                Log::warning('Unexpected type found when querying for register token. Presuming it was never set.');

                return null;
            }

            return $entry;
        } catch (\Throwable $th) {
            Log::error('Error during cache retrieval: '.$th->getMessage(), [$th]);
        }
    }

    public function clearRegisterToken(string $registerToken): void
    {
        try {
            Cache::delete("register-$registerToken");
        } catch (\Throwable $th) {
            Log::error('Error during cache deletion: '.$th->getMessage(), [$th]);
        }
    }

    public function putEmailAuthCode(string $email, string $code, ?DateTime $ttl): void
    {
        try {
            Cache::put("email-auth-$email", $code, $ttl ?? now()->addMinutes(15));
        } catch (\Throwable $th) {
            Log::error('Error during cache storage: '.$th->getMessage(), [$th]);

            return;
        }
    }

    public function getEmailAuthCode(string $email): ?string
    {
        try {
            $entry = Cache::get("email-auth-$email");

            if (! is_string($entry)) {
                Log::warning('Unexpected type found when querying for email auth code. Presuming it was never set.');

                return null;
            }

            return $entry;
        } catch (\Throwable $th) {
            Log::error('Error during cache retrieval: '.$th->getMessage(), [$th]);
        }
    }

    public function clearEmailAuthCode(string $email): void
    {
        try {
            Cache::delete("email-auth-$email");
        } catch (\Throwable $th) {
            Log::error('Error during cache deletion: '.$th->getMessage(), [$th]);
        }
    }
}
