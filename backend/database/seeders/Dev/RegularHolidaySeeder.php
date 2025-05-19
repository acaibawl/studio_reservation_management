<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

use App\Models\RegularHoliday;
use Illuminate\Database\Seeder;

class RegularHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RegularHoliday::factory()->count(1)->create();
    }
}
