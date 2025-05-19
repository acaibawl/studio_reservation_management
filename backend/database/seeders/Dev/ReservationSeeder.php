<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Reservation::factory()->count(1)->create();
    }
}
