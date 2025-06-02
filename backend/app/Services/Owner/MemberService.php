<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\Member;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    private const int PER_PAGE_COUNT = 20;

    public function fetchPaginatedMembers(array $attributes): array
    {
        $query = Member::with('reservations')->orderBy('id');
        if (isset($attributes['name'])) {
            $query->where('name', 'like', "%{$attributes['name']}%");
        }
        // ページサイズはページ番号指定クエリの適用前の総数に対して計算する必要がある
        $pageSize = ceil($query->count() / self::PER_PAGE_COUNT);

        if (isset($attributes['page'])) {
            $query->offset(($attributes['page'] - 1) * self::PER_PAGE_COUNT);
        }
        $query = $query->take(self::PER_PAGE_COUNT);

        $members = $query->get();

        return [$members, $pageSize];
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
