<?php

namespace App\Domain\Actions;

use App\Domain\DTO\RegisterFormData;
use App\Utils\Result;

/**
 * Registers a new user and authenticates them
 */
class Register
{
    /**
     * Executes the Action
     *
     * @return Result<string, \Exception> session token on success
     */
    public function execute(RegisterFormData $userData): Result
    {
        // Checks register token in cache to determine user e-mail and google ID

        // If register token is unavailable, return Failure

        // Creates new user in the database with provided data

        // Creates a session token for the new user and returns it
    }
}
