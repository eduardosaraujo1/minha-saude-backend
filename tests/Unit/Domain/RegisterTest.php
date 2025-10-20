<?php

namespace Tests\Unit\Domain;

use App\Data\Models\User;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * Business Requirements:
     * It registers a user in the database with specified info
     * It fails when invalid register token is provided
     * It fails when info is missing
     */

    // Test user registration with valid info
    public function test_register_google_user_with_valid_info(): void
    {
        // Arrange: Set up the necessary data for a valid user registration
        $data = [
            'name' => 'John Doe',
            'cpf' => '12345678909',
            'dataNascimento' => '1990-01-01',
            'telefone' => '11951490211',
        ];

        // Arrange: Set up a valid register token stored on Cache
    }

    public function test_fail_on_invalid_token(): void
    {
        //
    }

    public function test_fail_on_invalid_info(): void
    {
        //
    }
}
