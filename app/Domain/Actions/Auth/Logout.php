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
                return Result::failure(new \Exception('No authenticated user found'));
            }

            $user->tokens()->delete();

            return Result::success(null);
        } catch (\Exception $e) {
            return Result::failure($e);
        }
    }
}
