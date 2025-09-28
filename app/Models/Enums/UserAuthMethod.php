<?php

namespace App\Models\Enums;

enum UserAuthMethod
{
    case Google = "google";
    case Email = "email";
}