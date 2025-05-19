<?php

declare(strict_types=1);

namespace Database\Seeders\Prod;

use App\Models\BusinessTime;
use Illuminate\Database\Seeder;

class BusinessTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 営業時間は1件までとする仕様
        if (BusinessTime::count() > 0) {
            return;
        }
        BusinessTime::factory()->create([
            'open_time' => '10:00',
            'close_time' => '22:00',
        ]);
    }
}
