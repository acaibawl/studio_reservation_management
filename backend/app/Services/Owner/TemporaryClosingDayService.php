<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\TemporaryClosingDay;
use Illuminate\Database\Eloquent\Collection;

class TemporaryClosingDayService
{
    /**
     * @return Collection<int, TemporaryClosingDay>
     */
    public function getAll(): Collection
    {
        return TemporaryClosingDay::orderBy('date')->get();
    }

    public function create(array $attribute): TemporaryClosingDay
    {
        return TemporaryClosingDay::create([
            'date' => $attribute['date'],
        ]);
    }
}
