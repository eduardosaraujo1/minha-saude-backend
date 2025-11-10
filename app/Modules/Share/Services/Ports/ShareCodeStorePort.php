<?php

namespace App\Modules\Share\Services\Ports;

interface ShareCodeStorePort
{
    /**
     * Store document IDs associated with a generated share code.
     *
     * @param  array<int>  $documentIds  Array of document IDs
     * @return string The generated share code
     */
    public function store(array $documentIds): string;

    /**
     * Get document IDs associated with a share code.
     *
     * @return array<int> Array of document IDs
     */
    public function get(string $code): array;

    /**
     * Invalidate a share code.
     */
    public function invalidate(string $code): void;
}
