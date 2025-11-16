<?php

namespace App\Modules\Share\Logic;

use Carbon\Carbon;

class CalculateExpirationDate
{
    /**
     * Calculates the expiration date of a share
     *
     * - If the share has never been used, the expiration date is one day from its creation date.
     * - If the share has been used before, the expiration date is one hour from the first use date.
     *
     * @return void
     */
    public function execute(?Carbon $dtPrimeiroUso, Carbon $createdAt): Carbon
    {
        if ($dtPrimeiroUso === null) {
            return $createdAt->copy()->addDay();
        }

        return $dtPrimeiroUso->copy()->addHour();
    }
}
