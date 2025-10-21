<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendEmailCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_send_email_code(): void
    {
        $this->markTestSkipped('Test not implemented yet.');
    }
}
