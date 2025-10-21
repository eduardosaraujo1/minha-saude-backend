<?php

namespace App\Data\Services\Cache;

use App\Data\Services\Cache\DTO\RegisterTokenEntry;
use Cache;
use Log;

class CacheServiceImpl implements CacheService
{
    public function putRegisterToken(RegisterTokenEntry $entry): void
    {
        try {
            Cache::put($entry->token, $entry, $entry->ttl);
        } catch (\Throwable $th) {
            Log::error('Error during cache storage: '.$th->getMessage(), [$th]);

            return;
        }
    }

    public function getRegisterToken(string $registerToken): ?RegisterTokenEntry
    {
        try {
            $entry = Cache::get($registerToken);

            if (! $entry instanceof RegisterTokenEntry) {
                Log::warning('Unexpected type found when querying for register token. Presuming it was never set.');
            }

            return $entry;
        } catch (\Throwable $th) {
            Log::error('Error during cache retrieval: '.$th->getMessage(), [$th]);
        }
    }

    public function clearRegisterToken(string $registerToken): void
    {
        try {
            Cache::delete($registerToken);
        } catch (\Throwable $th) {
            Log::error('Error during cache deletion: '.$th->getMessage(), [$th]);
        }
    }
}
