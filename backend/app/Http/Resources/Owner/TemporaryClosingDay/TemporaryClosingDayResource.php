<?php

declare(strict_types=1);

namespace App\Http\Resources\Owner\TemporaryClosingDay;

use App\Models\TemporaryClosingDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemporaryClosingDayResource extends JsonResource
{
    /**
     * @var TemporaryClosingDay
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
            'date' => $this->resource->date->toDateString(),
        ];
    }
}
