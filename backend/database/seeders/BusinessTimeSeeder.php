<?php

namespace Database\Seeders;

use App\Models\BusinessTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
