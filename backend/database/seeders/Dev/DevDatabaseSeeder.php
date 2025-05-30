<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MemberSeeder::class,
            OwnerSeeder::class,
            StudioSeeder::class,
            TemporaryClosingDaySeeder::class,
            BusinessTimeSeeder::class,
            RegularHolidaySeeder::class,
            ReservationSeeder::class,
        ]);
    }
}
