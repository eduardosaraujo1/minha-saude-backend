<?php

namespace App\Modules\User\UseCases\Profile;

use App\Http\Exceptions\ApiException;
use App\Utils\Result;
use Carbon\Carbon;

class UpdateBirthdate
{
    /**
     * @return Result<null, ApiException>
     */
    public function execute(string $dataNascimento): Result
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return Result::failure(new ApiException('unauthorized', 401));
            }

            $user->update(['data_nascimento' => Carbon::parse($dataNascimento)]);

            return Result::success(null);
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
