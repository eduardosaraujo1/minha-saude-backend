<?php

use App\Data\Models\User;
use App\Data\Services\Cache\CacheService;

use function Pest\Laravel\mock;

// TO FIX: tests are not passing because Controller is erroring with a string not an exception

/** Business Requirements
 * it should succeed when the correct code is provided
 * it should fail when incorrect code is provided
 * it should fail when an unused e-mail is provided
 * it should fail when no code is provided
 * it should fail when no e-mail is provided
 */
test('it should succeed when the correct code is provided', function () {
    $email = 'user@example.com';
    $code = '123456';

    $cacheService = mock(CacheService::class);
    $cacheService->shouldReceive('getEmailAuthCode')
        ->with($email)
        ->andReturn($code);
    $cacheService->shouldReceive('clearEmailAuthCode')
        ->with($email);

    User::factory()->create(['email' => $email]);

    $response = $this->postJson(route('auth.login.email'), [
        'email' => $email,
        'codigoEmail' => $code,
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'isRegistered',
        'sessionToken',
    ]);
    expect($response['isRegistered'])->toBeTrue();
});

test('it should fail when incorrect code is provided', function () {
    $email = 'user@example.com';
    $correctCode = '123456';
    $incorrectCode = '654321';

    $cacheService = mock(CacheService::class);
    $cacheService->shouldReceive('getEmailAuthCode')
        ->with($email)
        ->andReturn($correctCode);

    $response = $this->postJson(route('auth.login.email'), [
        'email' => $email,
        'codigoEmail' => $incorrectCode,
    ]);

    $response->assertUnauthorized();
});

test('it should fail when an unused e-mail is provided', function () {
    $email = 'unused@example.com';
    $code = '123456';

    $cacheService = mock(CacheService::class);
    $cacheService->shouldReceive('getEmailAuthCode')
        ->with($email)
        ->andReturn(null);

    $response = $this->postJson(route('auth.login.email'), [
        'email' => $email,
        'codigoEmail' => $code,
    ]);

    $response->assertUnauthorized();
});

test('it should fail when no code is provided', function () {
    $email = 'user@example.com';

    $response = $this->postJson(route('auth.login.email'), [
        'email' => $email,
    ]);

    $response->assertUnprocessable();
});

test('it should fail when no e-mail is provided', function () {
    $code = '123456';

    $response = $this->postJson(route('auth.login.email'), [
        'codigoEmail' => $code,
    ]);

    $response->assertUnprocessable();
});
