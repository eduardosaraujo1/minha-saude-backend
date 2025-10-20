<?php

namespace Tests\Unit\Domain;

use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use App\Data\Services\DTO\GoogleUserInfo;
use App\Data\Services\Google\GoogleService;
use App\Domain\Actions\DTO\LoginResult;
use App\Domain\Actions\GoogleLogin;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * GoogleLogin Business Requirements:
 * - It returns a LoginResult indicating successful login when provided with a valid OAuth token for an existing user.
 * - It returns a LoginResult indicating unregistered user when provided with a valid OAuth token for a non-existing user.
 * - It returns a Failure Result when provided with an invalid OAuth token.
 */
class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticates_existing_user(): void
    {
        // Arrange: Set up Eloquent to have an existing user with the returned email (unsure if database should be created or mocked)
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => 'google-123',
            'metodo_autenticacao' => UserAuthMethod::Google,
        ]);
        $fakeOauthToken = 'valid-oauth-token';

        // Arrange: Set up a mock GoogleService to return user info for an existing user
        $mockGoogleService = $this->mock(GoogleService::class, function (\Mockery\MockInterface $mock) use ($existingUser, $fakeOauthToken) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with($fakeOauthToken)
                ->andReturn(Result::success(new GoogleUserInfo(
                    googleId: $existingUser->google_id,
                    email: $existingUser->email,
                )));
        });

        // Act
        $googleLogin = new GoogleLogin($mockGoogleService); // phpcs: ignore Mockery\MockInterface
        $result = $googleLogin->execute($fakeOauthToken);

        // Assert
        $this->assertTrue($result->isSuccess());
        $loginResult = $result->getOrThrow();
        $this->assertInstanceOf(LoginResult::class, $loginResult);
        $this->assertTrue($loginResult->isRegistered);
        $this->assertNotNull($loginResult->sessionToken);
        $this->assertNull($loginResult->registerToken);
    }

    public function test_generates_register_token(): void
    {
        // Arrange: Set up Eloquent to not find a user with the returned email
        // (No user created - database is empty)

        // Arrange: Set up a mock GoogleService to return user info for a non-existing user
        $mockGoogleService = $this->mock(GoogleService::class, function ($mock) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('valid-oauth-token')
                ->andReturn(Result::success(new GoogleUserInfo(
                    googleId: 'google-456',
                    email: 'newuser@example.com'
                )));
        });

        // Act: Call GoogleLogin with a valid OAuth token
        $googleLogin = new GoogleLogin($mockGoogleService); // phpcs: ignore
        $result = $googleLogin->execute('valid-oauth-token');

        // Assert: Verify the returned LoginResult indicates unregistered user
        $this->assertTrue($result->isSuccess());
        $loginResult = $result->getOrThrow();
        $this->assertInstanceOf(LoginResult::class, $loginResult);
        $this->assertFalse($loginResult->isRegistered);
        $this->assertNull($loginResult->sessionToken);
        $this->assertNotNull($loginResult->registerToken);

        // Assert: Verify the returned register token is stored in cache
        $cacheKey = "{$loginResult->registerToken}";
        $cachedData = Cache::get($cacheKey);
        $this->assertNotNull($cachedData);
        $this->assertEquals('newuser@example.com', $cachedData['email']);
        $this->assertEquals('google-456', $cachedData['google_id']);
    }

    public function test_google_service_fails_gracefully()
    {
        // Arrange: Set up a mock GoogleService to return a failure for an invalid OAuth token
        $mockGoogleService = $this->mock(GoogleService::class, function ($mock) {
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('invalid-oauth-token')
                ->andReturn(Result::failure(new \Exception(ExceptionDictionary::INVALID_OAUTH_TOKEN)));
        });

        // Arrange: Set up a Eloquent to fail test if it is called
        // (If GoogleService fails, we should never reach database queries)

        // Act: Call GoogleLogin with an invalid OAuth token
        $googleLogin = new GoogleLogin($mockGoogleService); // phpcs: ignore
        $result = $googleLogin->execute('invalid-oauth-token');

        // Assert: Verify the returned Result is a Failure
        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(\Exception::class, $result->tryGetFailure());
        $this->assertEquals(ExceptionDictionary::INVALID_OAUTH_TOKEN, $result->tryGetFailure()->getMessage());
    }
}
