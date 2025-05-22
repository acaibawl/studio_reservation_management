<?php

declare(strict_types=1);

namespace App\Domains\ReservationQuota;

interface ReservationQuotaInterface
{
    public function getHour(): int;

    public function toArray(): array;
}
