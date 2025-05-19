<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;

class GenerateReservationFinishAtService
{
    public function generate(Carbon $startAt, int $usageHour): Carbon
    {
        return $startAt->addHours($usageHour)->subSecond();
    }
}
