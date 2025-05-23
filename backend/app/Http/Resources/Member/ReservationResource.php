<?php

declare(strict_types=1);

namespace App\Http\Resources\Member;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * @var Reservation
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
            'id' => $this->resource->id,
            'studio_id' => $this->resource->studio_id,
            'studio_name' => $this->resource->studio->name,
            'start_at' => $this->resource->start_at->format('Y-m-d H:i:s'),
            'finish_at' => $this->resource->finish_at->format('Y-m-d H:i:s'),
        ];
    }
}
