<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Result é um padrão de projeto que encapsula o resultado de uma operação que pode ter
 * sucesso ou falha.
 *
 * Ao em vez de lançar exceções, uma função pode retornar um objeto Result que contém
 * o resultado da operação, permitindo que o chamador lide com o sucesso ou a falha de forma mais elegante.
 *
 * Isso é especialmente útil em linguagens que não suportam exceções ou quando se
 * deseja evitar o uso excessivo de exceções para controle de fluxo.
 *
 * A desvantagem é que o PHP é um pouco difícil de trabalhar com genéricos, então o uso de Result
 * pode ser um pouco verboso, precisando de um bloco de comentário em cima de cada função que o retorna.
 *
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
     * @template TNewError
     *
     * @param  TNewError  $error
     * @return Result<never, TNewError>
     */
    public static function failure(mixed $error): Result
    {
        return new Result(
            success: null,
            error: $error
        );
    }

    /**
     * Creates a result representing a successful outcome.
     *
     * @template TNewSuccess
     *
     * @param  TNewSuccess  $value
     * @return Result<TNewSuccess, never>
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
