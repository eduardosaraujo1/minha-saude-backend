<?php

namespace App\Domain\Actions\Auth\DTO;

use Carbon\Carbon;

class RegisterFormData
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public Carbon $dataNascimento,
        public string $telefone,
        public string $registerToken
    ) {}

    public function toArray(): array
    {
        return [
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'data_nascimento' => $this->dataNascimento->format('Y-m-d'),
            'telefone' => $this->telefone,
            'register_token' => $this->registerToken,
        ];
    }
}
