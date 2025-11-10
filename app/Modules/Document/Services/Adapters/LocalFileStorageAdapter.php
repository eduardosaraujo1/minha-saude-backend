<?php

namespace App\Modules\Document\Services\Adapters;

use App\Modules\Document\Services\Ports\FileStoragePort;
use Illuminate\Support\Facades\Storage;

class LocalFileStorageAdapter implements FileStoragePort
{
    /**
     * @param  \Psr\Http\Message\StreamInterface|\Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|resource  $content
     * @return bool|string
     */
    public function store(string $userId, string $uuid, mixed $content): bool
    {
        $path = $this->buildPath($userId, $uuid);

        return Storage::disk('local')->put($path, $content);
    }

    public function retrieve(string $userId, string $uuid): ?string
    {
        $path = $this->buildPath($userId, $uuid);

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    public function delete(string $userId, string $uuid): bool
    {
        $path = $this->buildPath($userId, $uuid);

        return Storage::disk('local')->delete($path);
    }

    private function buildPath(string $userId, string $uuid): string
    {
        return "{$userId}/{$uuid}";
    }
}
