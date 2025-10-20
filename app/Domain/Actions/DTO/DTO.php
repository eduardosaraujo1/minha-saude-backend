<?php

namespace App\Domain\Actions\DTO;

abstract class DTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
