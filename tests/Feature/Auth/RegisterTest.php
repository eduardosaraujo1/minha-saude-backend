<?php

use App\Data\Models\User;
use App\Data\Models\UserAuthMethod;
use App\Data\Services\Cache\CacheService;
use App\Data\Services\Cache\DTO\RegisterTokenEntry;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
            'nome_completo' => 'John Doe',
            'cpf' => '12345678909',
            'data_nascimento' => '2000-01-15',
            'telefone' => '11951490211',
            'email' => $email,
        ],
        'register_token' => $registerToken,
    ];

    // Act: Send POST request to register endpoint
    $response = $this->postJson(route('auth.register'), $registrationData);

    // Assert: Successful response
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'session_token',
            'user',
        ])
        ->assertJson([
            'status' => 'success',
        ]);

    // Assert: User was created in database with correct Google auth method
    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'cpf' => '12345678909',
        'email' => $email,
        'metodo_autenticacao' => UserAuthMethod::Google->value,
        'google_id' => $googleId,
        'data_nascimento' => Carbon::create(year: 2000, month: 1, day: 15),
        'telefone' => '11951490211',
    ]);

    // Assert: Session token is valid
    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
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
            'nome_completo' => 'Jane Doe',
            'cpf' => '98765432100',
            'data_nascimento' => '1995-05-20',
            'telefone' => '11987654321',
            'email' => $email,
        ],
        'register_token' => $registerToken,
    ];

    // Act: Send POST request to register endpoint
    $response = $this->postJson(route('auth.register'), $registrationData);

    // Assert: Successful response
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'session_token',
            'user',
        ])
        ->assertJson([
            'status' => 'success',
        ]);

    // Assert: User was created in database with Email auth method
    $this->assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'cpf' => '98765432100',
        'email' => $email,
        'metodo_autenticacao' => UserAuthMethod::Email->value,
        'google_id' => null,
        'data_nascimento' => Carbon::create(year: 1995, month: 5, day: 20),
        'telefone' => '11987654321',
    ]);

    // Assert: Session token is valid
    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->tokens)->toHaveCount(1);
});

test('does not register on missing token', function () {
    // Arrange: Set up registration data with invalid register token
    $invalidToken = 'invalid-register-token';
    $registrationData = [
        'user' => [
            'nome_completo' => 'Test User',
            'cpf' => '11122233344',
            'data_nascimento' => '1990-03-10',
            'telefone' => '11999887766',
            'email' => 'test@example.com',
        ],
        'register_token' => $invalidToken,
    ];

    // Act: Send POST request to register endpoint without setting up cache token
    $response = $this->postJson(route('auth.register'), $registrationData);

    // Assert: Request should fail
    $response->assertStatus(500);

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

    // Act & Assert: Test missing nome_completo
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'cpf' => '55566677788',
            'data_nascimento' => '1988-08-08',
            'telefone' => '11955566677',
            'email' => $email,
        ],
        'register_token' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.nome_completo']);

    // Act & Assert: Test missing cpf
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome_completo' => 'Empty User',
            'data_nascimento' => '1988-08-08',
            'telefone' => '11955566677',
            'email' => $email,
        ],
        'register_token' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.cpf']);

    // Act & Assert: Test missing data_nascimento
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome_completo' => 'Empty User',
            'cpf' => '55566677788',
            'telefone' => '11955566677',
            'email' => $email,
        ],
        'register_token' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.data_nascimento']);

    // Act & Assert: Test missing telefone
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome_completo' => 'Empty User',
            'cpf' => '55566677788',
            'data_nascimento' => '1988-08-08',
            'email' => $email,
        ],
        'register_token' => $registerToken,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user.telefone']);

    // Act & Assert: Test missing register_token
    $response = $this->postJson(route('auth.register'), [
        'user' => [
            'nome_completo' => 'Empty User',
            'cpf' => '55566677788',
            'data_nascimento' => '1988-08-08',
            'telefone' => '11955566677',
            'email' => $email,
        ],
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['register_token']);

    // Assert: No users were created during validation failures
    $this->assertDatabaseMissing('users', [
        'email' => $email,
    ]);
});