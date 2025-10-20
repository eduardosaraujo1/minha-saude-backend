<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * @template TSuccess
 * @template TError
 */
readonly class Result
{
    // Factory methods:
    // success, failure

    /**
     * @param  ?TSuccess  $success
     * @param  ?TError  $error
     */
    private function __construct(
        private mixed $success,
        private mixed $error,
    ) {}

    /**
     * Creates a result representing a failed outcome.
     *
     * @param  TError  $error
     * @return Result<never, TError>
     */
    public static function failure(mixed $error): Result
    {
        return new Result(
            success: null,
            error: $error
        );
    }

    /**
     * Creates a result representing a failed outcome.
     *
     * @param  TSuccess  $value
     * @return Result<TSuccess, never>
     */
    public static function success(mixed $value): Result
    {
        return new Result(
            success: $value,
            error: null
        );
    }

    // Accessor methods:
    // tryGetSuccess, tryGetFailure, isSuccess, isFailure, getOrThrow, fold, getBoth

    /**
     * Returns the successful value or null if the result is a failure.
     *
     * @return ?TSuccess
     */
    public function tryGetSuccess(): mixed
    {
        return $this->success;
    }

    /**
     * Returns the successful value or null if the result is a failure.
     *
     * @return ?TError
     */
    public function tryGetFailure(): mixed
    {
        return $this->error;
    }

    public function isSuccess(): bool
    {
        return $this->success !== null;
    }

    public function isFailure(): bool
    {
        return $this->error !== null;
    }

    /**
     * Gets the success value or throw an exception if it's a failure.
     *
     * @return TSuccess
     *
     * @throws \RuntimeException if the result is a failure.
     */
    public function getOrThrow(): mixed
    {
        if ($this->isFailure()) {
            throw new \RuntimeException('Attempted to get success value from a failure result.');
        }

        return $this->success;
    }

    /**
     * Provide a callback value to handle both success and failure cases.
     *
     * @template T
     *
     * @param  callable(TSuccess): T  $onSuccess
     * @param  callable(TError): T  $onFailure
     * @return T
     */
    public function fold(callable $onSuccess, callable $onFailure): mixed
    {
        if ($this->isSuccess()) {
            return $onSuccess($this->success);
        } else {
            return $onFailure($this->error);
        }
    }

    /**
     * @template T
     *
     * @param  callable(TSuccess): T  $callback
     * @return ?T
     */
    public function onSuccess(callable $callback): mixed
    {
        if ($this->isSuccess()) {
            return $callback($this->success);
        }

        return null;
    }
}
