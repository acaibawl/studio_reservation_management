<?php

declare(strict_types=1);

namespace App\Http\Resources\Reservation;

use App\Domains\Owner\ReservationQuota\ReservationQuotaInterface;
use App\ViewModels\Reservation\DailyQuotasStatus;
use App\ViewModels\Reservation\StudioReservationQuotas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyQuotasStatusResource extends JsonResource
{
    /**
     * @var DailyQuotasStatus
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->resource->date->toDateString(),
            'studios' => $this->resource->studioReservationQuotas->map(
                fn (StudioReservationQuotas $studioReservationQuotas) => [
                    'id' => $studioReservationQuotas->studio->id,
                    'name' => $studioReservationQuotas->studio->name,
                    'start_at' => $studioReservationQuotas->studio->start_at,
                    'reservation_quotas' => $studioReservationQuotas->reservationQuotas->map(
                        fn (ReservationQuotaInterface $reservationQuota) => $reservationQuota->toArray()
                    ),
                ]
            ),
        ];
    }
}
