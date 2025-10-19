<?php

namespace App\Data\DTO;

/**
 * Data Transfer Objects (DTOs) são utilizados para representar dados que serão enviados ou que foram recebidos
 * de outras fontes, seja do frontend ou de um serviço externo.
 *
 * Por exemplo:
 * - [GoogleUserInfo] é um objeto que será recebido do serviço externo do Google.
 *
 * Essa implementação (Data/DTO) serve para estruturar dados utilizados pelos Services.
 */
abstract class DTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
