<?php

namespace App\Modules\User\UseCases\Profile;

use App\Http\Exceptions\ApiException;
use App\Utils\Result;

class UpdateName
{
    /**
     * @return Result<null, ApiException>
     */
    public function execute(string $nome): Result
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return Result::failure(new ApiException('unauthorized', 401));
            }

            $user->update(['name' => $nome]);

            return Result::success(null);
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
