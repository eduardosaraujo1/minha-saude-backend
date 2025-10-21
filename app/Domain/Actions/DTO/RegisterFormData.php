<?php

namespace App\Domain\Actions\DTO;

use Carbon\Carbon;

class RegisterFormData extends DTO
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public Carbon $dataNascimento,
        public string $telefone,
        public string $registerToken
    ) {}
}
