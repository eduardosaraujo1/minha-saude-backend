<?php

namespace App\Domain\Actions\Auth;

use App\Utils\Result;

class Logout
{
    /**
     * Executes the Action
     *
     * @return Result<null, \Exception>
     */
    public function execute(): Result
    {
        try {
            $user = auth()->user();

            if (! $user) {
                // obs: this should not happen as the route is protected by auth middleware
                return Result::failure(new \Exception('No authenticated user found'));
            }

            $user->tokens()->delete();

            return Result::success(null);
        } catch (\Exception $e) {
            return Result::failure($e);
        }
    }
}
