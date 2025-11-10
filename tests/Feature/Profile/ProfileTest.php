<?php

use App\Modules\User\Models\User;

test('can get user profile', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'cpf' => '12345678909',
        'telefone' => '11987654321',
        'data_nascimento' => '1990-01-15',
    ]);

    // Act: Send GET request to profile endpoint
    $response = $this->actingAs($user)->getJson('/api/v1/profile');

    // Assert: Successful response with correct data
    $response->assertSuccessful()
        ->assertJson([
            'id' => $user->id,
            'nome' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'dataNascimento' => '1990-01-15',
        ]);
});

test('cannot get profile without authentication', function () {
    // Act: Send GET request without authentication
    $response = $this->getJson('/api/v1/profile');

    // Assert: Unauthorized response
    $response->assertUnauthorized();
});

test('can update user name', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create(['name' => 'Old Name']);

    // Act: Send PUT request to update name
    $response = $this->actingAs($user)->putJson('/api/v1/profile/name', [
        'nome' => 'New Name',
    ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Name was updated in database
    $user->refresh();
    expect($user->name)->toBe('New Name');
});

test('cannot update name without authentication', function () {
    // Act: Send PUT request without authentication
    $response = $this->putJson('/api/v1/profile/name', [
        'nome' => 'New Name',
    ]);

    // Assert: Unauthorized response
    $response->assertUnauthorized();
});

test('cannot update name with empty value', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();

    // Act: Send PUT request with empty name
    $response = $this->actingAs($user)->putJson('/api/v1/profile/name', [
        'nome' => '',
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nome']);
});

test('cannot update name without nome field', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();

    // Act: Send PUT request without nome field
    $response = $this->actingAs($user)->putJson('/api/v1/profile/name', []);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nome']);
});

test('can update user birthdate', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create(['data_nascimento' => '1990-01-01']);

    // Act: Send PUT request to update birthdate
    $response = $this->actingAs($user)->putJson('/api/v1/profile/birthdate', [
        'dataNascimento' => '1995-05-20',
    ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Birthdate was updated in database
    $user->refresh();
    expect($user->data_nascimento->format('Y-m-d'))->toBe('1995-05-20');
});

test('cannot update birthdate without authentication', function () {
    // Act: Send PUT request without authentication
    $response = $this->putJson('/api/v1/profile/birthdate', [
        'dataNascimento' => '1995-05-20',
    ]);

    // Assert: Unauthorized response
    $response->assertUnauthorized();
});

test('cannot update birthdate with invalid date format', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();

    // Act: Send PUT request with invalid date format
    $response = $this->actingAs($user)->putJson('/api/v1/profile/birthdate', [
        'dataNascimento' => '20-05-1995',
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dataNascimento']);
});

test('cannot update birthdate without dataNascimento field', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();

    // Act: Send PUT request without dataNascimento field
    $response = $this->actingAs($user)->putJson('/api/v1/profile/birthdate', []);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dataNascimento']);
});

test('can update user phone', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create(['telefone' => '11999999999']);

    // Act: Send PUT request to update phone
    $response = $this->actingAs($user)->putJson('/api/v1/profile/phone', [
        'telefone' => '11988888888',
    ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: Phone was updated in database
    $user->refresh();
    expect($user->telefone)->toBe('11988888888');
});

test('cannot update phone without authentication', function () {
    // Act: Send PUT request without authentication
    $response = $this->putJson('/api/v1/profile/phone', [
        'telefone' => '11988888888',
    ]);

    // Assert: Unauthorized response
    $response->assertUnauthorized();
});

test('cannot update phone with empty value', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();

    // Act: Send PUT request with empty phone
    $response = $this->actingAs($user)->putJson('/api/v1/profile/phone', [
        'telefone' => '',
    ]);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['telefone']);
});

test('cannot update phone without telefone field', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();

    // Act: Send PUT request without telefone field
    $response = $this->actingAs($user)->putJson('/api/v1/profile/phone', []);

    // Assert: Validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['telefone']);
});

test('can delete user profile', function () {
    // Arrange: Create a user and authenticate
    $user = User::factory()->create();
    $token = 'reauth_token';

    app()->make(\App\Modules\User\Services\Ports\CacheServicePort::class)
        ->putReauthenticateToken((string) $user->id, $token, null);

    // Act: Send DELETE request to delete profile
    $response = $this->actingAs($user)->deleteJson('/api/v1/profile', [
        'reauthToken' => $token,
    ]);

    // Assert: Successful response
    $response->assertSuccessful()
        ->assertJson([]);

    // Assert: User was soft deleted in database
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});
