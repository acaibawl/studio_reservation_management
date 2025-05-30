<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

use App\Models\Owner;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Owner::factory()->count(10)->create();
    }
}
