<?php

declare(strict_types=1);

namespace App\Http\Resources\Member;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowMeResource extends JsonResource
{
    /**
     * @var Member
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
            'email' => $this->resource->email,
            'name' => $this->resource->name,
            'address' => $this->resource->address,
            'tel' => $this->resource->tel,
        ];
    }
}
