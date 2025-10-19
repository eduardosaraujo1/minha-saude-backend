<?php

namespace App\Domain\DTO;

use Carbon\Carbon;

class RegisterFormData extends DTO
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public Carbon $dataNascimento,
        public Carbon $telefone,
        public string $registerToken
    ) {}
}
