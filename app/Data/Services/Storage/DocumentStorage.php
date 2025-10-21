<?php

namespace App\Data\Services\Storage;

use File;

interface DocumentStorage
{
    // TODO: see if this is the correct type for file content
    public function store(string $userId, string $uuid, File $content): bool;

    public function retrieve(string $userId, string $uuid): ?string;

    public function delete(string $userId, string $uuid): bool;
}
