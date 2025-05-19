<?php

declare(strict_types=1);

namespace App\Http\Resources\Reservation;

use App\ViewModels\Reservation\ReservationShow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationShowResource extends JsonResource
{
    /**
     * @var ReservationShow
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reservation = $this->resource->reservation;

        return [
            'reservation' => [
                'id' => $reservation->id,
                'studio_id' => $reservation->studio_id,
                'studio_name' => $reservation->studio->name,
                'start_at' => $reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => $this->resource->maxUsageHour,
                'member_id' => $reservation->member_id,
                'member_name' => $reservation->member->name,
                'memo' => $reservation->memo,
            ],
        ];
    }
}
