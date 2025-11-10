<?php

namespace App\Modules\User\UseCases\Auth;

use App\Http\Exceptions\ApiException;
use App\Utils\Result;

class Logout
{
    /**
     * Executes the Action
     *
     * @return Result<null, ApiException>
     */
    public function execute(): Result
    {
        try {
            $user = auth()->user();

            if (! $user) {
                // obs: this should not happen as the route is protected by auth middleware
                return Result::failure(ApiException::forbiddenError());
            }

            $user->tokens()->delete();

            return Result::success(null);
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
