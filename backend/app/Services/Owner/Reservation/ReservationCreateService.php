<?php

declare(strict_types=1);

namespace App\Services\Owner\Reservation;

use App\Exceptions\Reservation\AvailableHourExceededException;
use App\Mail\Member\Reservation\ReservationApplicationHaveBeenReceivedMail;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Studio;
use App\Services\GenerateReservationFinishAtService;
use App\Services\Reservation\EnsureCanReserve;
use Arr;
use Carbon\CarbonImmutable;
use DB;
use Mail;

readonly class ReservationCreateService
{
    public function __construct(
        private GenerateReservationFinishAtService $generateReservationFinishAtService,
        private EnsureCanReserve $ensureCanReserve,
    ) {}

    /**
     * @throws AvailableHourExceededException
     */
    public function create(Member $member, Studio $studio, array $attributes): Reservation
    {
        $usageHour = $attributes['usage_hour'];
        $startAt = CarbonImmutable::parse($attributes['start_at']);
        // テーブルをテーブルロックする
        DB::unprepared('LOCK TABLES reservations WRITE, business_times READ, regular_holidays READ, temporary_closing_days READ, studios READ');
        $this->ensureCanReserve->handle(
            $studio,
            $startAt,
            $usageHour
        );

        $reservation = Reservation::create([
            'member_id' => $member->id,
            'studio_id' => $studio->id,
            'start_at' => $attributes['start_at'],
            'finish_at' => $this->generateReservationFinishAtService->generate(
                $startAt,
                $usageHour
            ),
            'memo' => Arr::get($attributes, 'memo', null),
        ]);

        Mail::send(new ReservationApplicationHaveBeenReceivedMail($member, $reservation));

        return $reservation;
    }
}
