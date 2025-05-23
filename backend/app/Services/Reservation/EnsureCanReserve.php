<?php

declare(strict_types=1);

namespace App\Services\Reservation;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Models\Studio;
use App\Services\Owner\Reservation\StudioMaxAvailableHourService;
use Carbon\CarbonImmutable;

readonly class EnsureCanReserve
{
    public function __construct(
        private StudioMaxAvailableHourService $studioMaxAvailableHourService,
    ) {}

    /**
     * @throws AvailableHourExceededException
     */
    public function handle(int $studioId, CarbonImmutable $startAt, int $usageHour)
    {
        $studio = Studio::where(['id' => $studioId])->firstOrFail();
        $maxAvailableHour = $this->studioMaxAvailableHourService->getByDate(
            $studio,
            $startAt,
            $startAt->hour,
        );
        // 利用可能上限時間を超えた場合はエラーをスローする
        if ($maxAvailableHour < $usageHour) {
            throw new AvailableHourExceededException();
        }
    }
}
