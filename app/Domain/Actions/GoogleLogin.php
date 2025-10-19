<?php

namespace App\Domain\Actions;

use App\Domain\DTO\LoginResult;

class GoogleLogin
{
    public function execute(string $oauthToken): LoginResult
    {
        // Use GoogleService to get e-mail and Google ID from the OAuth token

        // If e-mail exists in the database, retrieve the user, generate token, and return LoginResult

        // If e-mail does not exist, generate a register token, store in cache, and return LoginResult indicating not registered
    }
}
