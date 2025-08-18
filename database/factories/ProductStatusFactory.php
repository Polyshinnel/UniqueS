<?php

namespace Database\Factories;

use App\Models\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductStatus>
 */
class ProductStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['В работе', 'В продаже', 'Резерв', 'Холд', 'Продано', 'Вторая очередь', 'Отказ']),
            'color' => $this->faker->hexColor(),
            'active' => $this->faker->boolean(),
            'must_active_adv' => $this->faker->boolean(),
        ];
    }
}
