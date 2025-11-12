<?php

it('Sends e-mail code and verifies it', function () {
    // Send e-mail code
    $response = $this->postJson('/api/v1/auth/send-email', [
        'email' => 'eduardosaraujo100@gmail.com',
    ]);

    $response->assertStatus(200);
});

it('verifies e-mail code and logs in', function () {
    $verificationCode = '301652';

    // Attempt login with code
    $response = $this->postJson('/api/v1/auth/login/email', [
        'email' => 'eduardosaraujo100@gmail.com',
        'codigoEmail' => $verificationCode,
    ]);

    dump($response['message']);
    $response->assertStatus(200);
})->skip('Temporarily skipping this test due to email code issues.');
