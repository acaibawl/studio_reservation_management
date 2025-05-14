<?php

namespace App\Services\Owner;

use App\Models\BusinessTime;

class BusinessTimeService
{
    /**
     * 1件しか存在しないのでfirstOfFailでよい
     * @return BusinessTime
     */
    public function get(): BusinessTime
    {
        return BusinessTime::firstOrFail();
    }

    /**
     * @param array $attribute
     * @return bool
     */
    public function update(array $attribute): bool
    {
        return BusinessTime::firstOrFail()->update($attribute['business_time']);
    }
}
