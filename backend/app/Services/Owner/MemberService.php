<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\Member;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    private const int PAGE_SIZE = 20;

    /**
     * @return Collection<int, Member>
     */
    public function fetchPaginatedMembers(array $attributes): Collection
    {
        $query = Member::orderBy('id')->take(self::PAGE_SIZE);
        if (isset($attributes['page'])) {
            $query->offset(($attributes['page'] - 1) * self::PAGE_SIZE);
        }
        if (isset($attributes['name'])) {
            $query->where('name', 'like', "%{$attributes['name']}%");
        }

        return $query->get();
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getFutureReservations(Member $member): Collection
    {
        return $member->reservations()
            ->with('studio')
            ->where('finish_at', '>=', now())
            ->orderBy('start_at')
            ->orderBy('finish_at')
            ->orderByRaw('studio_id')
            ->get();
    }
}
