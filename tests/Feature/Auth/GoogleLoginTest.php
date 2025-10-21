<?php

namespace Tests\Feature\Auth;

use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use App\Data\Services\Cache\CacheService;
use App\Data\Services\Google\DTO\UserInfo;
use App\Data\Services\Google\GoogleService;
use App\Domain\Exceptions\ExceptionDictionary;
use App\Utils\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_authenticates_user(): void
    {
        // Arrange: Create an existing user with Google authentication
        $fakeGoogleId = '38267382323';
        $fakeServerAuth = '4/0jdsSksfr93Ljsdnu2...';
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
        $this->assertNotNull($response->json('sessionToken'));

        // Assert: User has a new token
        $user->refresh();
        $this->assertCount(1, $user->tokens);
    }

    public function test_requests_registration_on_unregistered_user(): void
    {
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
        $this->assertNotNull($registerToken);
        $this->assertIsString($registerToken);

        // Assert: Register token is stored in cache with correct data
        $cachedEntry = app(CacheService::class)->getRegisterToken($registerToken);
        $this->assertNotNull($cachedEntry);
        $this->assertEquals($fakeEmail, $cachedEntry->email);
        $this->assertEquals($fakeGoogleId, $cachedEntry->googleId);

        // Assert: No user was created
        $this->assertDatabaseMissing('users', [
            'email' => $fakeEmail,
            'google_id' => $fakeGoogleId,
        ]);
    }

    public function test_returns_500_on_unreachable_google_service(): void
    {
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

        // Assert: Request fails with 500 error (GoogleLogin wraps any failure as INVALID_OAUTH_TOKEN)
        $response->assertStatus(500);
    }

    public function test_returns_401_on_invalid_oauth_token(): void
    {
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
    }

    public function test_validation_fails_on_missing_token(): void
    {
        // Act: Send POST request without tokenOauth
        $response = $this->postJson(route('auth.login.google'), []);

        // Assert: Validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tokenOauth']);
    }
}
