<?php

namespace Tests\Unit\Data;

use App\Data\Services\Google\GoogleServiceImpl;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleServiceTest extends TestCase
{
    public function test_gets_correct_data_with_valid_code(): void
    {
        // Mock the Socialite driver
        $mockUser = new \Laravel\Socialite\Two\User;
        $mockUser->id = '123456789';
        $mockUser->email = 'test@example.com';
        $mockUser->name = 'Test User';

        $mockDriver = $this->mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $mockDriver->shouldReceive('getAccessTokenResponse')
            ->once()
            ->with('valid-auth-code')
            ->andReturn(['access_token' => 'mock-access-token']); // phpcs: ignore

        $mockDriver->shouldReceive('userFromToken')
            ->once()
            ->with('mock-access-token')
            ->andReturn($mockUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($mockDriver);

        $mockDriver->shouldReceive('stateless')
            ->andReturnSelf();

        $mockDriver->shouldReceive('redirectUrl')
            ->andReturnSelf();

        // Act
        $googleService = new GoogleServiceImpl;
        $result = $googleService->getUserInfo('valid-auth-code');

        // Assert
        $this->assertTrue(
            $result->isSuccess(),
            'Expected successful result from GoogleServiceImpl. Got "'.($result->tryGetFailure()?->getMessage()).'".'
        );

        $value = $result->getOrThrow();
        $this->assertEquals('123456789', $value->googleId);
        $this->assertEquals('test@example.com', $value->email);
    }

    public function test_handles_invalid_code_gracefully(): void
    {
        // Mock the Socialite driver to throw an exception
        $mockDriver = $this->mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $mockDriver->shouldReceive('getAccessTokenResponse')
            ->once()
            ->with('invalid-auth-code')
            ->andThrow(new \Exception('Invalid authorization code'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($mockDriver);

        $mockDriver->shouldReceive('stateless')
            ->andReturnSelf();

        $mockDriver->shouldReceive('redirectUrl')
            ->andReturnSelf();

        // Act
        $googleService = new GoogleServiceImpl;
        $result = $googleService->getUserInfo('invalid-auth-code');

        // Assert
        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(\Exception::class, $result->tryGetFailure());
    }

    /**
     * Integration test - requires a fresh authorization code from Google
     * Only run this manually with a fresh code from https://developers.google.com/oauthplayground/
     *
     * To run: vendor/bin/phpunit --filter=test_integration_with_real_google_api
     */
    public function test_integration_with_real_google_api(): void
    {
        $this->markTestSkipped(
            'This test requires a fresh OAuth code from Google OAuth Playground. '.
            'Get one from https://developers.google.com/oauthplayground/ and update the $code variable, '.
            'then remove this markTestSkipped() line to run the test. '.
            'Remember: Authorization codes expire in 60 seconds and are single-use only!'
        );

        // Aviso: essa variável deve ser substituída por um token OAuth válido para o teste funcionar
        // Não se esqueça de removê-la após o uso para evitar problemas de segurança
        $code = 'SERVER_AUTHORIZATION_TOKEN';

        $googleService = new GoogleServiceImpl;
        $result = $googleService->getUserInfo($code);

        $this->assertTrue(
            $result->isSuccess(),
            'Expected successful result from GoogleServiceImpl. Got "'.($result->tryGetFailure()?->getMessage()).'".'
        );

        $value = $result->getOrThrow();
        $this->assertNotEmpty($value->email);
        $this->assertNotEmpty($value->googleId);

        // Output for manual verification
        dump('Google ID: '.$value->googleId);
        dump('Email: '.$value->email);
    }
}
