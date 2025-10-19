<?php

namespace App\Domain\DTO;

/**
 * Data Transfer Objects (DTOs) são utilizados para representar dados que serão enviados ou que foram recebidos
 * de outras fontes, seja do frontend ou de um serviço externo.
 *
 * Por exemplo:
 * - [LoginResult] é um objeto que será enviado ao frontend após uma tentativa de login.
 * - [RegisterFormData] é um objeto que representa os dados recebidos do frontend para registrar um novo usuário.
 *
 * Essa implementação (Domain/DTO) serve para estruturar dados utilizados pelas Actions.
 */
abstract class DTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
