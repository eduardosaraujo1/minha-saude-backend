<?php

namespace App\Domain\Actions\Auth\DTO;

use Carbon\Carbon;

class RegisterFormData
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

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
