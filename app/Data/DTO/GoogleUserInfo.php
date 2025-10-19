<?php

namespace App\Data\DTO;

class GoogleUserInfo extends DTO
{
    public function __construct(
        public string $id,
        public string $email,
    ) {}
}
