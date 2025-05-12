<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TemporaryClosingDay>
 */
class TemporaryClosingDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1週間前から2ヶ月後までの臨時休業日を作成
        return [
            'date' => fake()->dateTimeBetween('-1 week', '+2 month'),
        ];
    }
}
