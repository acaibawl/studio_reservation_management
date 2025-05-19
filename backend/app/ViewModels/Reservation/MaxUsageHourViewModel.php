<?php

declare(strict_types=1);

namespace App\ViewModels\Reservation;

use App\Models\Studio;
use Carbon\CarbonImmutable;

readonly class MaxUsageHourViewModel
{
    public function __construct(
        public Studio $studio,
        public CarbonImmutable $date,
        public int $hour,
        public int $maxUsageHour,
    ) {}
}
