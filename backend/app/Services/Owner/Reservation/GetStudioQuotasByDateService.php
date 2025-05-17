<?php

namespace App\Services\Owner\Reservation;

use App\Domains\Owner\ReservationQuota\Available;
use App\Domains\Owner\ReservationQuota\NotAvailable;
use App\Domains\Owner\ReservationQuota\ReservationQuotaInterface;
use App\Domains\Owner\ReservationQuota\Reserved;
use App\Enums\Studio\StartAt;
use App\Models\BusinessTime;
use App\Models\RegularHoliday;
use App\Models\Reservation;
use App\Models\Studio;
use App\Models\TemporaryClosingDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

readonly class GetStudioQuotasByDateService
{
    public function __construct(
        private ReservationQuotaFactory $reservationQuotaFactory,
    )
    {
    }

    public function get(CarbonImmutable $date): array
    {
        $studios = Studio::with('reservations')->get();
        $businessTime = BusinessTime::firstOrFail();
        $regularHolidays = RegularHoliday::get();
        $temporaryClosingDays = TemporaryClosingDay::get();
        return $studios->map(
            function (Studio $studio) use ($date, $businessTime, $regularHolidays, $temporaryClosingDays) {
                $reservationQuotas = collect(range(0, 23))->map(
                    function (int $hour) use ($date, $studio, $businessTime, $regularHolidays, $temporaryClosingDays) {
                        return $this->reservationQuotaFactory->generate(
                            $date,
                            $hour,
                            $studio,
                            $businessTime,
                            $regularHolidays,
                            $temporaryClosingDays
                        )->toArray();
                    }
                );

                return [
                    'id' => $studio->id,
                    'name' => $studio->name,
                    'start_at' => $studio->start_at->value,
                    'reservation_quotas' => $reservationQuotas,
                ];
            }
        );
    }
}
