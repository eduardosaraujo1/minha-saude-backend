<?php

namespace App\Modules\Document\Services\Ports;

interface FileStoragePort
{
    /**
     * Store file content for a user.
     *
     * @param  string  $userId  The user identifier
     * @param  string  $uuid  The unique file identifier
     * @param  mixed  $content  File content (string, resource, or UploadedFile)
     * @return bool True if stored successfully
     */
    public function store(string $userId, string $uuid, mixed $content): bool;

    /**
     * Retrieve file content for a user.
     *
     * @param  string  $userId  The user identifier
     * @param  string  $uuid  The unique file identifier
     * @return string|null File content or null if not found
     */
    public function retrieve(string $userId, string $uuid): ?string;

    /**
     * Delete file for a user.
     *
     * @param  string  $userId  The user identifier
     * @param  string  $uuid  The unique file identifier
     * @return bool True if deleted successfully
     */
    public function delete(string $userId, string $uuid): bool;
}
