<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\Member;
use App\Models\Reservation;
use App\Services\GenerateReservationFinishAtService;
use App\Services\Reservation\EnsureCanReserve;
use Arr;
use Carbon\CarbonImmutable;

readonly class ReservationCreateService
{
    public function __construct(
        private GenerateReservationFinishAtService $generateReservationFinishAtService,
        private EnsureCanReserve $ensureCanReserve,
    ) {}

    /**
     * @throws AvailableHourExceededException
     */
    public function create(Member $member, array $attributes): Reservation
    {
        $studioId = $attributes['studio_id'];
        $usageHour = $attributes['usage_hour'];
        $startAt = CarbonImmutable::parse($attributes['start_at']);
        $this->ensureCanReserve->handle(
            $studioId,
            $startAt,
            $usageHour
        );

        return Reservation::create([
            'member_id' => $member->id,
            'studio_id' => $studioId,
            'start_at' => $attributes['start_at'],
            'finish_at' => $this->generateReservationFinishAtService->generate(
                $startAt,
                $usageHour
            ),
            'memo' => Arr::get($attributes, 'memo', null),
        ]);
    }
}
