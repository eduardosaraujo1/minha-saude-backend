<?php

namespace App\Data\Models;

enum UserAuthMethod: string
{
    case Google = 'google';
    case Email = 'email';
}
