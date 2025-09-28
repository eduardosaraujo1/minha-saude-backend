<?php

namespace App\Models\Enums;

enum UserAuthMethod: string
{
    case Google = "google";
    case Email = "email";
}