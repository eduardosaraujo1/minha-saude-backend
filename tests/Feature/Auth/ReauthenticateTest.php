<?php

use App\Modules\User\DTOs\Auth\UserAuthMethod;
use App\Modules\User\DTOs\Google\UserInfo;
use App\Modules\User\Models\User;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Modules\User\Services\Ports\GoogleServicePort;
use App\Utils\Result;

it('reauthenticates user with google credentials', function () {
    $fakeGoogleId = 'google-id-123';
    $fakeOauthToken = 'fake-oauth-token';

    $googleUser = User::factory()->create([
        'google_id' => $fakeGoogleId,
        'metodo_autenticacao' => UserAuthMethod::Google,
        'email' => 'google@example.com',
    ]);

    $this->mock(GoogleServicePort::class, function ($mock) use ($fakeOauthToken, $fakeGoogleId) {
        $mock->shouldReceive('getUserInfo')
            ->once()
            ->with($fakeOauthToken)
            ->andReturn(Result::success(
                new UserInfo($fakeGoogleId, 'google@example.com')
            ));
    });

    $this->actingAs($googleUser);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'google',
        'auth' => [
            'google' => [
                'oauthToken' => $fakeOauthToken,
            ],
            'email' => null,
        ],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['reauthToken']);

    $reauthToken = $response->json('reauthToken');
    expect($reauthToken)->toBeString()->not->toBeEmpty();

    $cachedUserId = app(CacheServicePort::class)->getReauthenticateToken($reauthToken);
    expect($cachedUserId)->toBe((string) $googleUser->id);
});

it('reauthenticates user with email credentials', function () {
    $email = 'email@example.com';
    $code = '123456';

    $emailUser = User::factory()->create([
        'email' => $email,
        'metodo_autenticacao' => UserAuthMethod::Email,
    ]);

    app(CacheServicePort::class)->putEmailAuthCode($email, $code, now()->addMinutes(15));

    $this->actingAs($emailUser);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'email',
        'auth' => [
            'google' => null,
            'email' => [
                'email' => $email,
                'code' => $code,
            ],
        ],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['reauthToken']);

    $reauthToken = $response->json('reauthToken');
    expect($reauthToken)->toBeString()->not->toBeEmpty();

    $cachedUserId = app(CacheServicePort::class)->getReauthenticateToken($reauthToken);
    expect($cachedUserId)->toBe((string) $emailUser->id);
});

it('fails when google oauth token is invalid', function () {
    $googleUser = User::factory()->create([
        'google_id' => 'google-123',
        'metodo_autenticacao' => UserAuthMethod::Google,
    ]);

    $this->mock(GoogleServicePort::class, function ($mock) {
        $mock->shouldReceive('getUserInfo')
            ->once()
            ->andReturn(Result::failure(new \Exception('Invalid token')));
    });

    $this->actingAs($googleUser);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'google',
        'auth' => [
            'google' => [
                'oauthToken' => 'invalid-token',
            ],
            'email' => null,
        ],
    ]);

    $response->assertNotFound();
});

it('fails when email code is incorrect', function () {
    $email = 'test@example.com';
    $correctCode = '123456';
    $wrongCode = '654321';

    $emailUser = User::factory()->create([
        'email' => $email,
        'metodo_autenticacao' => UserAuthMethod::Email,
    ]);

    app(CacheServicePort::class)->putEmailAuthCode($email, $correctCode, now()->addMinutes(15));

    $this->actingAs($emailUser);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'email',
        'auth' => [
            'google' => null,
            'email' => [
                'email' => $email,
                'code' => $wrongCode,
            ],
        ],
    ]);

    $response->assertNotFound();
});

it('fails when user does not exist', function () {
    $user = User::factory()->create();

    $this->mock(GoogleServicePort::class, function ($mock) {
        $mock->shouldReceive('getUserInfo')
            ->once()
            ->andReturn(Result::success(
                new UserInfo('non-existent-google-id', 'nonexistent@example.com')
            ));
    });

    $this->actingAs($user);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'google',
        'auth' => [
            'google' => [
                'oauthToken' => 'some-token',
            ],
            'email' => null,
        ],
    ]);

    $response->assertNotFound();
});

it('validates required fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('auth.reauthenticate'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['authType', 'auth']);
});

it('validates authType must be google or email', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'invalid',
        'auth' => [],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['authType']);
});

it('validates google oauth token is required when authType is google', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'google',
        'auth' => [
            'google' => [],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['auth.google.oauthToken']);
});

it('validates email and code are required when authType is email', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'email',
        'auth' => [
            'email' => [],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['auth.email.email', 'auth.email.code']);
});

it('requires authentication', function () {
    $response = $this->postJson(route('auth.reauthenticate'), [
        'authType' => 'email',
        'auth' => [
            'email' => [
                'email' => 'test@example.com',
                'code' => '123456',
            ],
        ],
    ]);

    $response->assertForbidden();
});
