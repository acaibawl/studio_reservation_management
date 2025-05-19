<?php

declare(strict_types=1);

namespace App\Http\Resources\Reservation;

use App\ViewModels\Reservation\ReservationShow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
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
        return [
            'reservation' => [
                'id' => $this->resource->reservation->id,
                'studio_id' => $this->resource->reservation->studio_id,
                'studio_name' => $this->resource->reservation->studio->name,
                'start_at' => $this->resource->reservation->start_at->format('Y-m-d H:i:s'),
                'finish_at' => $this->resource->reservation->finish_at->format('Y-m-d H:i:s'),
                'max_usage_hour' => $this->resource->maxUsageHour,
                'member_id' => $this->resource->reservation->member_id,
                'member_name' => $this->resource->reservation->member->name,
                'memo' => $this->resource->reservation->memo,
            ],
        ];
    }
}
