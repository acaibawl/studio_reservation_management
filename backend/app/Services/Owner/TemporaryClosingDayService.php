<?php

namespace App\Services\Owner;

use App\Models\TemporaryClosingDay;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class TemporaryClosingDayService
{
    /**
     * @return Collection<TemporaryClosingDay>
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
