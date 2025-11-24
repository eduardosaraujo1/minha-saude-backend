<?php

namespace App\Modules\Share\Services\Adapters;

use App\Modules\Share\Services\Ports\ShareCodeStorePort;
use Illuminate\Support\Facades\Cache;

class CacheShareCodeStoreAdapter implements ShareCodeStorePort
{
    /**
     * Store document IDs associated with a generated share code.
     *
     * @param  array<int>  $documentIds  Array of document IDs
     * @return string The generated share code
     */
    public function store(array $documentIds): string
    {
        $code = $this->generateShareCode();

        // Store the document IDs with the code (24 hours expiry)
        Cache::put("share:$code", $documentIds, now()->addHours(24));

        return $code;
    }

    /**
     * Get document IDs associated with a share code.
     *
     * @return array<int> Array of document IDs
     */
    public function get(string $code): array
    {
        $documentIds = Cache::get("share:$code");

        if (! is_array($documentIds)) {
            return [];
        }

        return $documentIds;
    }

    /**
     * Invalidate a share code by removing it from cache.
     */
    public function invalidate(string $code): void
    {
        Cache::forget("share:$code");
    }

    /**
     * Generate a unique share code.
     */
    protected function generateShareCode(): string
    {
        do {
            $code = $this->generateRandomCode();
        } while (Cache::has("share:$code"));

        return $code;
    }

    /**
     * Generate a random 8-character share code.
     */
    protected function generateRandomCode(): string
    {
        $timestamp = now()->timestamp;
        $random = random_int(1000, 9999);

        return strtoupper(substr("SHARE{$timestamp}{$random}", -8));
    }
}
