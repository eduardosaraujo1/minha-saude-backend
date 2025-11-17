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
    public function store(string $userId, string $documentId, mixed $content): bool
    {
        $directory = $userId;
        $filename = "{$documentId}.pdf";

        $storeResult = Storage::disk('local')->putFileAs($directory, $content, $filename);

        return $storeResult !== false;
    }

    public function retrieve(string $userId, string $documentId): ?string
    {
        $path = $this->buildPath($userId, $documentId);

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    public function delete(string $userId, string $documentId): bool
    {
        $path = $this->buildPath($userId, $documentId);

        return Storage::disk('local')->delete($path);
    }

    private function buildPath(string $userId, string $documentId): string
    {
        return "{$userId}/{$documentId}.pdf";
    }
}
