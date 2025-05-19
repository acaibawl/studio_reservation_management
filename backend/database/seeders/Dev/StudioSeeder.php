<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

use App\Models\Studio;
use Illuminate\Database\Seeder;

class StudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Studio::factory()->count(10)->create();
    }
}
