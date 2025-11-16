<?php

namespace App\Modules\User\UseCases\Profile;

use App\Http\Exceptions\ApiException;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Utils\Result;

class RequestDeletion
{
    public function __construct(public CacheServicePort $cacheService) {}

    /**
     * Summary of execute
     *
     * @return Result<void, ApiException>
     */
    public function execute(string $reauthToken): Result
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return Result::failure(new ApiException('unauthorized', 401));
            }

            // Check if $user->id has the same reauthentication token as stored in CacheServicePort
            $value = $this->cacheService->getReauthenticateToken($reauthToken);

            if ($value !== (string) $user->id) {
                return Result::failure(new ApiException('invalid_reauth_token', 403));
            }

            // Proceed with the deletion request
            $user->delete();

            return Result::success(null);
        } catch (\Exception $e) {
            return Result::failure(ApiException::unexpectedError());
        }
    }
}
