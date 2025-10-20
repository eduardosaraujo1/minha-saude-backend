<?php

namespace App\Domain\Enums;

enum UserAuthMethod: string
{
    case Google = 'google';
    case Email = 'email';
}
