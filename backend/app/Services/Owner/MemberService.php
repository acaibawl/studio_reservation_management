<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\Member;

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

        return [
            'members' => $members,
            'pageSize' => $pageSize,
        ];
    }
}
