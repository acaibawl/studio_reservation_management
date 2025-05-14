<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Models\BusinessTime;

class BusinessTimeService
{
    /**
     * 1件しか存在しないのでfirstOfFailでよい
     */
    public function get(): BusinessTime
    {
        return BusinessTime::firstOrFail();
    }

    public function update(array $attribute): bool
    {
        return BusinessTime::firstOrFail()->update($attribute['business_time']);
    }
}
