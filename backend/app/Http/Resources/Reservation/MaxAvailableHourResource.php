<?php

declare(strict_types=1);

namespace App\Http\Resources\Reservation;

use App\ViewModels\Reservation\MaxAvailableHourViewModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaxAvailableHourResource extends JsonResource
{
    /**
     * @var MaxAvailableHourViewModel
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
            'studio_id' => $this->resource->studio->id,
            'studio_name' => $this->resource->studio->name,
            'date' => $this->resource->date->toDateString(),
            'hour' => $this->resource->hour,
            'max_available_hour' => $this->resource->maxAvailableHour,
        ];
    }
}
