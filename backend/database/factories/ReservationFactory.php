<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'studio_id' => Studio::factory(),
            'start_at' => Carbon::create('2025', '05', '18', '18', '00', '00', 'JST'),
            'finish_at' => Carbon::create('2025', '05', '19', '00', '00', '00', 'JST'),
            'memo' => fake()->sentence(),
        ];
    }
}
