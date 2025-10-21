<?php

namespace App\Domain\Exceptions;

class ExceptionDictionary
{
    public const INVALID_OAUTH_TOKEN = 'invalid_oauth_token';

    public const INVALID_REGISTER_TOKEN = 'invalid_register_token';

    public const INCORRECT_AUTH_CODE = 'incorrect_auth_code';

    public const EMAIL_NOT_FOUND = 'email_not_found';
}
