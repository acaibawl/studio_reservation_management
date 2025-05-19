<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

use App\Models\BusinessTime;
use Illuminate\Database\Seeder;

class BusinessTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessTime::factory()->count(1)->create();
    }
}
