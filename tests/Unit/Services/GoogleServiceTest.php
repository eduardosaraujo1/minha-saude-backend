<?php

use App\Data\Services\Google\GoogleServiceImpl;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Mockery\MockInterface;

test('gets correct data with valid code', function () {
    // Mock the Socialite driver
    $mockUser = new \Laravel\Socialite\Two\User;
    $mockUser->id = '123456789';
    $mockUser->email = 'test@example.com';
    $mockUser->name = 'Test User';

    $this->instance(
        GoogleProvider::class,
        Mockery::mock(GoogleProvider::class, function (MockInterface $mock) use ($mockUser) {

            $mock->shouldReceive('getAccessTokenResponse')
                ->once()
                ->with('valid-auth-code')
                // phpcs:ignore
                ->andReturn(['access_token' => 'mock-access-token']);

            $mock->shouldReceive('userFromToken')
                ->once()
                ->with('mock-access-token')
                ->andReturn($mockUser);

            Socialite::shouldReceive('driver')
                ->with('google')
                ->andReturn($mock);

            $mock->shouldReceive('stateless')
                ->andReturnSelf();

            $mock->shouldReceive('redirectUrl')
                ->andReturnSelf();
        }));

    // Act
    $googleService = new GoogleServiceImpl;
    $result = $googleService->getUserInfo('valid-auth-code');

    // Assert
    expect($result->isSuccess())->toBeTrue('Expected successful result from GoogleServiceImpl. Got "'.($result->tryGetFailure()?->getMessage()).'".');

    $value = $result->getOrThrow();
    expect($value->googleId)->toEqual('123456789');
    expect($value->email)->toEqual('test@example.com');
});

test('handles invalid code gracefully', function () {
    // Mock the Socialite driver to throw an exception
    $mockDriver = $this->mock(GoogleProvider::class);
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
    expect($result->isFailure())->toBeTrue();
    expect($result->tryGetFailure())->toBeInstanceOf(\Exception::class);
});

test('integration with real google api', function () {
    $this->markTestSkipped(
        'This test requires a fresh OAuth code from the mobile app. '.
        'Get one using the Flutter app debugger, '.
        'then remove this markTestSkipped() line to run the test. '.
        'Remember: Authorization codes expire in 60 seconds and are single-use only!'
    );

    // Aviso: essa variável deve ser substituída por um token OAuth válido para o teste funcionar
    // Não se esqueça de removê-la após o uso para evitar problemas de segurança
    $code = 'SERVER_AUTHORIZATION_TOKEN';

    $googleService = new GoogleServiceImpl;
    $result = $googleService->getUserInfo($code);

    expect($result->isSuccess())->toBeTrue('Expected successful result from GoogleServiceImpl. Got "'.($result->tryGetFailure()?->getMessage()).'".');

    $value = $result->getOrThrow();
    expect($value->email)->not->toBeEmpty();
    expect($value->googleId)->not->toBeEmpty();

    // Output for manual verification
    dump('Google ID: '.$value->googleId);
    dump('Email: '.$value->email);
});
