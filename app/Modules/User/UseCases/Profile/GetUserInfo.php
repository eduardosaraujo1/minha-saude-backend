<?php

namespace App\Modules\User\UseCases\Profile;

use App\Http\Exceptions\ApiException;
use App\Modules\User\DTOs\Profile\ProfileDto;
use App\Utils\Result;

class GetUserInfo
{
    /**
     * @return Result<ProfileDto, ApiException>
     */
    public function execute(): Result
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return Result::failure(new ApiException('unauthorized', 401));
            }

            $profileDto = ProfileDto::fromUser($user);

            return Result::success($profileDto);
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
