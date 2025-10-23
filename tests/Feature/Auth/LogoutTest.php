<?php

use App\Data\Models\User;
use App\Utils\Constants;

/** Business Requirements
 * It logs out an authenticated user by invalidating their session token.
 * It fails if no user is authenticated
 */
it('logs out an authenticated user', function () {
    // Arrange: Create and authenticate a user
    $user = User::factory()->create();
    $token = $user->createToken(Constants::DEFAULT_SANCTUM_TOKEN_NAME)->plainTextToken;

    // Act: Call the logout endpoint
    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson(route('auth.logout'));

    // Assert: Check response status and message
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    // Assert: Ensure the token is invalidated
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name' => Constants::DEFAULT_SANCTUM_TOKEN_NAME,
    ]);
});

it('fails to log out when no user is authenticated', function () {
    // Act: Call the logout endpoint without authentication
    $response = $this->postJson(route('auth.logout'));

    // Assert: Check response status
    $response->assertStatus(401); // Unauthorized
});
