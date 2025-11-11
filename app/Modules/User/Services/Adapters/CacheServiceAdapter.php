<?php

namespace App\Modules\User\Services\Adapters;

use App\Modules\User\DTOs\Cache\RegisterTokenEntry;
use App\Modules\User\Services\Ports\CacheServicePort;
use Cache;
use DateTime;
use Log;

class CacheServiceAdapter implements CacheServicePort
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
            var_dump("[DEBUG] Stored code $code for email $email");
        } catch (\Throwable $th) {
            Log::error('Error during cache storage: '.$th->getMessage(), [$th]);

            return;
        }
    }

    public function getEmailAuthCode(string $email): ?string
    {
        try {
            var_dump("[DEBUG] Retrieving code for email $email");
            $entry = Cache::get("email-auth-$email");
            var_dump('[DEBUG] Retrieved entry: '.var_export($entry, true));

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

    public function putReauthenticateToken(string $userId, string $token, ?DateTime $ttl): void
    {
        try {
            Cache::put("reauth-$token", $userId, $ttl ?? now()->addMinutes(15));
        } catch (\Throwable $th) {
            Log::error('Error during cache storage: '.$th->getMessage(), [$th]);

            return;
        }
    }

    public function getReauthenticateToken(string $token): ?string
    {
        try {
            $userId = Cache::get("reauth-$token");

            if (! is_string($userId)) {
                Log::warning('Unexpected type found when querying for reauth token. Presuming it was never set.');

                return null;
            }

            return $userId;
        } catch (\Throwable $th) {
            Log::error('Error during cache retrieval: '.$th->getMessage(), [$th]);

            return null;
        }
    }

    public function clearReauthenticateToken(string $token): void
    {
        try {
            Cache::delete("reauth-$token");
        } catch (\Throwable $th) {
            Log::error('Error during cache deletion: '.$th->getMessage(), [$th]);
        }
    }
}
