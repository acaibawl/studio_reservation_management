<?php

declare(strict_types=1);

namespace App\Http\Resources\Reservation;

use App\ViewModels\Reservation\MaxUsageHourViewModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaxUsageHourResource extends JsonResource
{
    /**
     * @var MaxUsageHourViewModel
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
            'max_usage_hour' => $this->resource->maxUsageHour,
        ];
    }
}
