<?php

namespace App\Enums;

enum UserAuthMethod: string
{
    case Google = 'google';
    case Email = 'email';
}
