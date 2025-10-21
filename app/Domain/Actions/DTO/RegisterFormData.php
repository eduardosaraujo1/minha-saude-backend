<?php

namespace App\Domain\Actions\DTO;

use Carbon\Carbon;

class RegisterFormData extends DTO
{
    public Carbon $dataNascimento;

    public function __construct(
        public string $nome,
        public string $cpf,
        string|Carbon $dataNascimento,
        public string $telefone,
        public string $registerToken
    ) {
        $this->dataNascimento = $dataNascimento instanceof Carbon
            ? $dataNascimento
            : Carbon::parse($dataNascimento);
    }
}
