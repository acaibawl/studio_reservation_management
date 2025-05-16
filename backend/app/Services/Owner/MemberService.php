<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    private const int PAGE_SIZE = 20;

    /**
     * @return Collection<int, Member>
     */
    public function index(array $attributes): Collection
    {
        $query = Member::with('reservations')->orderBy('id')->take(self::PAGE_SIZE);
        if (isset($attributes['page'])) {
            $query->offset(($attributes['page'] - 1) * self::PAGE_SIZE);
        }
        if (isset($attributes['name'])) {
            $query->where('name', 'like', "%{$attributes['name']}%");
        }

        return $query->get();
    }
}
