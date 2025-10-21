<?php

use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use App\Data\Services\Cache\CacheService;
use App\Data\Services\Google\DTO\UserInfo;
use App\Data\Services\Google\GoogleService;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Result;

test('google login authenticates user', function () {
    $fakeGoogleId = '38267382323';
    $fakeServerAuth = '4/0jdsSksfr93Ljsdnu2...';

    // Arrange: Create an existing user with Google authentication
    $user = User::factory()->create([
        'metodo_autenticacao' => UserAuthMethod::Google,
        'google_id' => $fakeGoogleId,
        'email' => 'existing@example.com',
    ]);

    // Arrange: Mock GoogleService to return user info for existing user
    $this->mock(
        GoogleService::class,
        function (\Mockery\MockInterface $mock) use ($user, $fakeGoogleId, $fakeServerAuth) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with($fakeServerAuth)
                ->andReturn(Result::success(
                    new UserInfo(
                        $fakeGoogleId,
                        $user->email,
                    )
                ));
        });

    // Act: Send POST request to Google login endpoint
    $response = $this->postJson(route('auth.login.google'), [
        'tokenOauth' => $fakeServerAuth,
    ]);

    // Assert: Successful authentication response
    $response->assertStatus(200)
        ->assertJsonStructure([
            'isRegistered',
            'sessionToken',
            'registerToken',
        ])
        ->assertJson([
            'isRegistered' => true,
            'registerToken' => null,
        ]);

    // Assert: Session token is present and valid
    expect($response->json('sessionToken'))->not->toBeNull();

    // Assert: User has a new token
    $user->refresh();
    expect($user->tokens)->toHaveCount(1);
});

test('requests registration on unregistered user', function () {
    // Arrange: Mock GoogleService to return user info for non-existing user
    $fakeServerAuth = '4/0jdsSksfr93Ljsdnu2...';
    $fakeGoogleId = 'google-new-user-456';
    $fakeEmail = 'newuser@example.com';

    $this->mock(
        GoogleService::class,
        function (\Mockery\MockInterface $mock) use ($fakeServerAuth, $fakeGoogleId, $fakeEmail) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with($fakeServerAuth)
                ->andReturn(Result::success(
                    new UserInfo(
                        $fakeGoogleId,
                        $fakeEmail
                    )
                ));
        });

    // Act: Send POST request to Google login endpoint
    $response = $this->postJson(route('auth.login.google'), [
        'tokenOauth' => $fakeServerAuth,
    ]);

    // Assert: Response indicates registration is needed
    $response->assertStatus(200)
        ->assertJsonStructure([
            'isRegistered',
            'sessionToken',
            'registerToken',
        ])
        ->assertJson([
            'isRegistered' => false,
            'sessionToken' => null,
        ]);

    // Assert: Register token is present
    $registerToken = $response->json('registerToken');
    expect($registerToken)->not->toBeNull();
    expect($registerToken)->toBeString();

    // Assert: Register token is stored in cache with correct data
    $cachedEntry = app(CacheService::class)->getRegisterTokenData($registerToken);
    expect($cachedEntry)->not->toBeNull();
    expect($cachedEntry->email)->toEqual($fakeEmail);
    expect($cachedEntry->googleId)->toEqual($fakeGoogleId);

    // Assert: No user was created
    $this->assertDatabaseMissing('users', [
        'email' => $fakeEmail,
        'google_id' => $fakeGoogleId,
    ]);
});

test('returns client error on unreachable google service', function () {
    // Arrange: Mock GoogleService to return a failure (treated as invalid OAuth token)
    $fakeServerAuth = 'invalid-oauth-token';

    $this->mock(
        GoogleService::class,
        function (\Mockery\MockInterface $mock) use ($fakeServerAuth) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with($fakeServerAuth)
                ->andReturn(Result::failure(
                    new \Exception('Google service unreachable')
                ));
        });

    // Act: Send POST request to Google login endpoint with invalid token
    $response = $this->postJson(route('auth.login.google'), [
        'tokenOauth' => $fakeServerAuth,
    ]);

    $response->assertClientError();
});

test('returns 401 on invalid oauth token', function () {
    // Arrange: Mock GoogleService to return invalid OAuth token error
    $fakeServerAuth = 'invalid-oauth-token';

    $this->mock(
        GoogleService::class,
        function (\Mockery\MockInterface $mock) use ($fakeServerAuth) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with($fakeServerAuth)
                ->andReturn(Result::failure(
                    new \Exception(ExceptionDictionary::INVALID_OAUTH_TOKEN)
                ));
        });

    // Act: Send POST request to Google login endpoint with invalid token
    $response = $this->postJson(route('auth.login.google'), [
        'tokenOauth' => $fakeServerAuth,
    ]);

    // Assert: Request fails with 401 unauthorized
    $response->assertStatus(401);
});

test('validation fails on missing token', function () {
    // Act: Send POST request without tokenOauth
    $response = $this->postJson(route('auth.login.google'), []);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tokenOauth']);
});
