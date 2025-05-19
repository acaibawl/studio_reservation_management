<?php

declare(strict_types=1);

namespace Database\Seeders\Prod;

use Illuminate\Database\Seeder;

class ProdDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BusinessTimeSeeder::class,
            MemberSeeder::class,
        ]);
    }
}
