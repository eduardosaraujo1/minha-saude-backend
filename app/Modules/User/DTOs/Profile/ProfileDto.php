<?php

namespace App\Modules\User\DTOs\Profile;

use App\Modules\User\Models\User;

class ProfileDto
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $cpf,
        public string $email,
        public ?string $telefone,
        public ?string $dataNascimento,
        public string $metodoAutenticacao
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id,
            nome: $user->name,
            cpf: $user->cpf,
            email: $user->email,
            telefone: $user->telefone,
            dataNascimento: $user->data_nascimento?->format('Y-m-d'),
            metodoAutenticacao: $user->metodo_autenticacao->value
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'dataNascimento' => $this->dataNascimento,
            'metodoAutenticacao' => $this->metodoAutenticacao,
        ];
    }
}
