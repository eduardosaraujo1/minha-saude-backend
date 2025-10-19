<?php

namespace App\Domain\Actions;

use App\Domain\DTO\RegisterFormData;

class Register
{
    public function execute(RegisterFormData $userData): string
    {
        // Checks register token in cache to determine user e-mail and google ID

        // If register token is unavailable, return Failure

        // Creates new user in the database with provided data

        // Creates a session token for the new user and returns it
    }
}
