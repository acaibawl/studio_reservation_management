<?php

namespace App\Services\Owner;

use App\Models\RegularHoliday;
use Illuminate\Database\Eloquent\Collection;

class RegularHolidayService
{
    /**
     * @return Collection<int, RegularHoliday>
     */
    public function getAll(): Collection
    {
        return RegularHoliday::orderBy('code')->get();
    }

    /**
     * 引数の内容でテーブルの内容をまるっと置き換える
     * @param array $attribute
     * @return int
     */
    public function update(array $attribute): int
    {
        $regularHolidayCodes = $attribute['regular_holidays'];
        RegularHoliday::whereNotIn('code', $regularHolidayCodes)->delete();
        return RegularHoliday::upsert(
            collect($regularHolidayCodes)->map(fn (int $code) => [
                'code' => $code,
            ])->toArray(),
            ['code'],
            ['code']
        );
    }
}
