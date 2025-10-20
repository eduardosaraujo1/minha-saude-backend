<?php

namespace App\Domain\Actions;

use App\Data\Services\Google\GoogleService;
use App\Domain\DTO\LoginResult;
use App\Utils\Result;

/**
 * This Action handles login via Google OAuth token and returns the appropriate LoginResult
 */
class GoogleLogin
{
    public function __construct(private GoogleService $googleService) {}

    /**
     * Executes the Action
     *
     * @return Result<LoginResult, \Exception>
     */
    public function execute(string $oauthToken): Result
    {
        try {
            // code...
        } catch (\Exception $e) {
            return Result::failure($e);
        }
        // Use GoogleService to get e-mail and Google ID from the OAuth token

        // If e-mail exists in the database, retrieve the user, generate token, and return LoginResult

        // If e-mail does not exist, generate a register token, store in cache, and return LoginResult indicating not registered
    }
}
