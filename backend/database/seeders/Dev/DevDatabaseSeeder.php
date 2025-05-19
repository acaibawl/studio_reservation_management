<?php

declare(strict_types=1);

namespace Database\Seeders\Dev;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Dev\BusinessTimeSeeder;
use Database\Seeders\Dev\MemberSeeder;
use Database\Seeders\Dev\OwnerSeeder;
use Database\Seeders\Dev\RegularHolidaySeeder;
use Database\Seeders\Dev\ReservationSeeder;
use Database\Seeders\Dev\StudioSeeder;
use Database\Seeders\Dev\TemporaryClosingDaySeeder;
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
