<?php

use App\Modules\Document\Services\Adapters\LocalFileStorageAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('stores file content successfully', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId = 'user-123';
    $uuid = 'doc-456';
    $content = 'Test file content';

    $result = $adapter->store($userId, $uuid, $content);

    expect($result)->toBeTrue();
    expect(Storage::disk('local')->exists("{$userId}/{$uuid}.pdf"))->toBeTrue();
});

it('stores uploaded file successfully', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId = 'user-123';
    $uuid = 'doc-789';
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $result = $adapter->store($userId, $uuid, $file);

    expect($result)->toBeTrue();
    expect(Storage::disk('local')->exists("{$userId}/{$uuid}.pdf"))->toBeTrue();
});

it('retrieves file content successfully', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId = 'user-123';
    $uuid = 'doc-456';
    $content = 'Test file content';

    $adapter->store($userId, $uuid, $content);
    $retrieved = $adapter->retrieve($userId, $uuid);

    expect($retrieved)->toBe($content);
});

it('returns null when retrieving non-existent file', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId = 'user-123';
    $uuid = 'non-existent';

    $retrieved = $adapter->retrieve($userId, $uuid);

    expect($retrieved)->toBeNull();
});

it('deletes file successfully', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId = 'user-123';
    $uuid = 'doc-456';
    $content = 'Test file content';

    $adapter->store($userId, $uuid, $content);
    $result = $adapter->delete($userId, $uuid);

    expect($result)->toBeTrue();
    expect(Storage::disk('local')->exists("{$userId}/{$uuid}.pdf"))->toBeFalse();
});

it('returns true when deleting non-existent file', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId = 'user-123';
    $uuid = 'non-existent';

    $result = $adapter->delete($userId, $uuid);

    expect($result)->toBeTrue();
});

it('organizes files by user id', function () {
    $adapter = new LocalFileStorageAdapter;
    $userId1 = 'user-123';
    $userId2 = 'user-456';
    $uuid = 'doc-same';
    $content1 = 'User 1 content';
    $content2 = 'User 2 content';

    $adapter->store($userId1, $uuid, $content1);
    $adapter->store($userId2, $uuid, $content2);

    expect($adapter->retrieve($userId1, $uuid))->toBe($content1);
    expect($adapter->retrieve($userId2, $uuid))->toBe($content2);
});
