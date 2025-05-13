<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Studio\StartAt;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Studio>
 */
class StudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name().'スタジオ',
            'start_at' => Arr::random(StartAt::cases()),
        ];
    }
}
