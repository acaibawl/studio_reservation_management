<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TemporaryClosingDay;
use Illuminate\Database\Seeder;

class TemporaryClosingDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TemporaryClosingDay::factory()->count(10)->create();
    }
}
