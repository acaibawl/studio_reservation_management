<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\RegularHoliday;
use Arr;
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
     */
    public function update(array $attribute): int
    {
        // regular_holidaysが存在しない場合を考慮
        $regularHolidayCodes = Arr::get($attribute, 'regular_holidays', []);
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
