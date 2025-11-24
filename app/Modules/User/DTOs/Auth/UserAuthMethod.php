<?php

namespace App\Modules\User\DTOs\Auth;

enum UserAuthMethod: string
{
    case Google = 'google';
    case Email = 'email';
}
