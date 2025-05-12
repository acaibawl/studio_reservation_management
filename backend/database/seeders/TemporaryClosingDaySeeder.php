<?php

namespace Database\Seeders;

use App\Models\TemporaryClosingDay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
