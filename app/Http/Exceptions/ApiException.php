<?php

namespace App\Http\Exceptions;

class ApiException
{
    public function __construct(
        public string $message = 'unexpected_error',
        public int $code = 500
    ) {}

    public static function unexpectedError(): self
    {
        return new self('unexpected_error', 500);
    }

    public static function invalidOauthToken(): self
    {
        return new self('invalid_oauth_token', 401);
    }

    public static function invalidRegisterToken(): self
    {
        return new self('invalid_register_token', 400);
    }

    public static function incorrectAuthCode(): self
    {
        return new self('incorrect_auth_code', 403);
    }

    public static function forbiddenError(): self
    {
        return new self('forbidden_error', 403);
    }

    public static function emailNotFound(): self
    {
        return new self('email_not_found', 404);
    }
}
