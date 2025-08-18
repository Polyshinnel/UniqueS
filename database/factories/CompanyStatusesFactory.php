<?php

namespace Database\Factories;

use App\Models\CompanyStatuses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyStatuses>
 */
class CompanyStatusesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['В работе', 'Вторая очередь', 'Холд', 'Отказ']),
            'color' => $this->faker->hexColor(),
            'active' => true,
        ];
    }
}
