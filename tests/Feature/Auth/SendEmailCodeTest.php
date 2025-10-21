<?php

use App\Domain\Mail\AuthVerificationCode;

/** Business Requirements
 * E-mail content must have code
 * When e-mail is provided then e-mail service sends e-mail
 */
test('has the provided code as plain text', function () {
    $fakeCode = '123456';

    $mailable = new AuthVerificationCode($fakeCode);

    $mailable->assertSeeInHtml($fakeCode);
});

test('can send email code to provided e-mail', function () {
    Mail::fake();

    $email = 'eduardosaraujo100@gmail.com';

    $response = $this->post(route('auth.send.email'), ['email' => $email]);

    // Assert any e-mail was sent to $email
    $response->assertSuccessful();
    Mail::assertSent(AuthVerificationCode::class, 1);
    Mail::assertSent(AuthVerificationCode::class, $email);
});

// Debugging: actually send the e-mail
test('actually sends email', function () {
    $email = 'eduardosaraujo100@gmail.com';

    $this->post(route('auth.send.email'), ['email' => $email]);
});
