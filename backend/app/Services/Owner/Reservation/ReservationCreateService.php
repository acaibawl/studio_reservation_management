<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\Reservation;
use App\Models\Studio;
use App\Services\GenerateReservationFinishAtService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

readonly class ReservationCreateService
{
    const int OWNER_MEMBER_ID = 9999999;

    public function __construct(
        private GenerateReservationFinishAtService $generateReservationFinishAtService,
        private StudioMaxAvailableHourService $studioMaxAvailableHourService,
    ) {}

    /**
     * @throws AvailableHourExceededException
     */
    public function create(array $attributes): Reservation
    {
        $usageHour = $attributes['usage_hour'];
        $studio = Studio::where(['id' => $attributes['studio_id']])->firstOrFail();
        $targetDateTime = CarbonImmutable::parse($attributes['start_at']);
        $maxAvailableHour = $this->studioMaxAvailableHourService->getByDate(
            $studio,
            $targetDateTime,
            $targetDateTime->hour,
        );
        if ($maxAvailableHour < $usageHour) {
            throw new AvailableHourExceededException();
        }

        return Reservation::create([
            'member_id' => self::OWNER_MEMBER_ID,
            'studio_id' => $attributes['studio_id'],
            'start_at' => $attributes['start_at'],
            'finish_at' => $this->generateReservationFinishAtService->generate(
                Carbon::parse($attributes['start_at']),
                $usageHour
            ),
            'memo' => $attributes['memo'],
        ]);
    }
}
