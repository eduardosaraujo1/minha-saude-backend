<?php

namespace App\Utils;

/**
 * Constants that are used by the application that are too permanent to be put in .env but too volatile to be used implicitly in code.
 *
 * Currently, only includes the Laravel Sanctum personal access token string required to create personal access tokens.
 */
class Constants
{
    public const DEFAULT_SANCTUM_TOKEN_NAME = 'session-token';
}
