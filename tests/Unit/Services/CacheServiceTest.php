<?php

use App\Data\Services\Cache\CacheServiceImpl;
use App\Data\Services\Cache\DTO\RegisterTokenEntry;

/** Business Requirements
 * stores, gets and clears register tokens in cache
 * stores, gets and clears email authentication codes in cache
 */
test('stores gets and clears register token in cache', function () {
    $service = new CacheServiceImpl;

    // Arrange: put a register token in cache through CacheService
    $entry = new RegisterTokenEntry(
        token: 'test-token-123',
        email: 'user@example.com',
        googleId: null,
        ttl: 3600
    );
    $service->putRegisterToken($entry);

    // Act: get the register token from cache through CacheService
    $retrieved = $service->getRegisterTokenData('test-token-123');

    // Assert: check that the retrieved token matches the stored one
    expect($retrieved)->not->toBeNull();
    expect($retrieved->token)->toEqual('test-token-123');
    expect($retrieved->email)->toEqual('user@example.com');
    expect($retrieved->googleId)->toBeNull();

    // Act: clear the register token from cache through CacheService
    $service->clearRegisterToken('test-token-123');

    // Assert: check that the token is no longer in cache through another get statement
    $afterClear = $service->getRegisterTokenData('test-token-123');
    expect($afterClear)->toBeNull();
});

test('stores gets and clears email auth code in cache', function () {
    $service = new CacheServiceImpl;

    // Arrange: put an email auth code in cache through CacheService
    $email = 'test@example.com';
    $code = '123456';
    $ttl = (new DateTime)->modify('+15 minutes');
    $service->putEmailAuthCode($email, $code, $ttl);

    // Act: get the email auth code from cache through CacheService
    $retrieved = $service->getEmailAuthCode($email);

    // Assert: check that the retrieved code matches the stored one
    expect($retrieved)->toEqual('123456');

    // Act: clear the email auth code from cache through CacheService
    $service->clearEmailAuthCode($email);

    // Assert: check that the code is no longer in cache through another get statement
    $afterClear = $service->getEmailAuthCode($email);
    expect($afterClear)->toBeNull();
});
