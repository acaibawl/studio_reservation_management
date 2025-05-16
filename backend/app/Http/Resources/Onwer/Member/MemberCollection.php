<?php

declare(strict_types=1);

namespace App\Http\Resources\Onwer\Member;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MemberCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['members' => $this->collection->map(fn (Member $member) => [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'has_reservation' => $member->hasReservation(),
        ]),
        ];
    }
}
