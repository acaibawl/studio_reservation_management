<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;

class GenerateReservationFinishAtService
{
    public function generate(CarbonImmutable $startAt, int $usageHour): CarbonImmutable
    {
        return $startAt->addHours($usageHour)->subSecond();
    }
}
