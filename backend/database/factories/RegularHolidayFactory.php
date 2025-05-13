<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\WeekDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegularHoliday>
 */
class RegularHolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => \Arr::random(WeekDay::cases()),
        ];
    }
}
