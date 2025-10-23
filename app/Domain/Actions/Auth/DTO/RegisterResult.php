<?php

namespace App\Domain\Actions\Auth\DTO;

use App\Data\Models\User;

class RegisterResult
{
    public function __construct(
        public string $sessionToken,
        public User $user,
    ) {}

    public function toArray(): array
    {
        return [
            'sessionToken' => $this->sessionToken,
            'user' => [
                'nome' => $this->user->name,
                'cpf' => $this->user->cpf,
                'dataNascimento' => $this->user->data_nascimento?->format('Y-m-d'),
                'telefone' => $this->user->telefone,
            ],
        ];
    }
}
