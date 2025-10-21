<?php

use App\Data\Models\User;
use App\Data\Services\Cache\CacheService;
use App\Data\Services\Cache\DTO\RegisterTokenEntry;

// uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('register with google register token', function () {
    // Arrange: Set up a valid Google register token
    $registerToken = 'fake-google-register-token';
    $googleId = '2187438292';
    $email = 'john.doe@gmail.com';

    app(CacheService::class)->putRegisterToken(new RegisterTokenEntry(
        token: $registerToken,
        email: $email,
        googleId: $googleId,
        ttl: now()->addMinutes(15)
    ));

    // Arrange: Set up registration data
    $registrationData = [
        'user' => [
            'nome' => 'John Doe',
            'cpf' => '12345678909',
            'dataNascimento' => '2000-01-15',
            'telefone' => '11951490211',
        ],
        'registerToken' => $registerToken,
    ];

    // Act: Send POST request to register endpoint
    $response = $this->postJson(route('auth.register'), $registrationData);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJsonStructure([
            'sessionToken',
            'user' => [
                'nome',
                'cpf',
                'telefone',
                'dataNascimento',
            ],
        ]);

    // Assert: User was created in database with correct Google auth method
    $user = User::where('email', $email)->first();
    expect($user->name)->toEqual($registrationData['user']['nome']);
    expect($user->cpf)->toEqual($registrationData['user']['cpf']);
    expect($user->data_nascimento->format('Y-m-d'))->toEqual($registrationData['user']['dataNascimento']);
    expect($user->telefone)->toEqual($registrationData['user']['telefone']);

    // Assert: Session token is valid
    expect($user)->toBeTruthy();
    expect($user->tokens)->toHaveCount(1);
});

test('register with email register token', function () {
    // Arrange: Set up a valid Email register token (no Google ID)
    $registerToken = 'fake-email-register-token';
    $email = 'jane.doe@example.com';

    app(CacheService::class)->putRegisterToken(new RegisterTokenEntry(
        token: $registerToken,
        email: $email,
        googleId: null,
        ttl: now()->addMinutes(15)
    ));

    // Arrange: Set up registration data
    $registrationData = [
        'user' => [
            'nome' => 'Jane Doe',
            'cpf' => '98765432100',
            'dataNascimento' => '1995-05-20',
            'telefone' => '11987654321',
            'email' => $email,
        ],
        'registerToken' => $registerToken,
    ];

    // Act: Send POST request to register endpoint
    $response = $this->postJson(route('auth.register'), $registrationData);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJsonStructure([
            'sessionToken',
            'user' => [
                'nome',
                'cpf',
                'telefone',
                'dataNascimento',
            ],
        ]);

    // Assert: User was created in database with correct Google auth method
    $user = User::where('email', $email)->first();
    expect($user->name)->toEqual($registrationData['user']['nome']);
    expect($user->cpf)->toEqual($registrationData['user']['cpf']);
    expect($user->data_nascimento->format('Y-m-d'))->toEqual($registrationData['user']['dataNascimento']);
    expect($user->telefone)->toEqual($registrationData['user']['telefone']);

    // Assert: Session token is valid
    expect($user)->toBeTruthy();
    expect($user->tokens)->toHaveCount(1);
});

test('does not register on missing token', function () {
    // Arrange: Set up registration data with invalid register token
    $invalidToken = 'invalid-register-token';
    $registrationData = [
        'user' => [
            'nome' => 'Test User',
            'cpf' => '11122233344',
            'dataNascimento' => '1990-03-10',
            'telefone' => '11999887766',
            'email' => 'test@example.com',
        ],
        'registerToken' => $invalidToken,
    ];

    // Act: Send POST request to register endpoint without setting up cache token
    $response = $this->postJson(route('auth.register'), $registrationData);

    // Assert: Request should fail
    $response->assertStatus(401);

    // Based on the Register action catching exceptions
    // Assert: User was NOT created in database
    $this->assertDatabaseMissing('users', [
        'cpf' => '11122233344',
        'email' => 'test@example.com',
    ]);
});

test('does not accept empty metadata', function () {
    // Arrange: Set up a valid register token
    $registerToken = 'valid-token-empty-metadata';
    $email = 'empty@example.com';

    app(CacheService::class)->putRegisterToken(new RegisterTokenEntry(
        token: $registerToken,
        email: $email,
        googleId: null,
        ttl: now()->addMinutes(15)
    ));

    // Act & Assert: Test missing nome
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'cpf' => '55566677788',
            'dataNascimento' => '1988-08-08',
            'telefone' => '11955566677',
            'email' => $email,
        ],
        'registerToken' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.nome']);

    // Act & Assert: Test missing cpf
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome' => 'Empty User',
            'dataNascimento' => '1988-08-08',
            'telefone' => '11955566677',
            'email' => $email,
        ],
        'registerToken' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.cpf']);

    // Act & Assert: Test missing dataNascimento
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome' => 'Empty User',
            'cpf' => '55566677788',
            'telefone' => '11955566677',
            'email' => $email,
        ],
        'registerToken' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.dataNascimento']);

    // Act & Assert: Test missing telefone
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome' => 'Empty User',
            'cpf' => '55566677788',
            'dataNascimento' => '1988-08-08',
            'email' => $email,
        ],
        'registerToken' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.telefone']);

    // Act & Assert: Test missing registerToken
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome' => 'Empty User',
            'cpf' => '55566677788',
            'dataNascimento' => '1988-08-08',
            'telefone' => '11955566677',
            'email' => $email,
        ],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['registerToken']);

    // Assert: No users were created during validation failures
    $this->assertDatabaseMissing('users', [
        'email' => $email,
    ]);
});
