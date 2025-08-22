<?php

namespace Database\Factories;

use App\Models\Advertisement;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertisement>
 */
class AdvertisementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'category_id' => ProductCategories::factory(),
            'product_id' => Product::factory(),
            'creator_id' => User::factory(),
            'main_characteristics' => $this->faker->paragraph(),
            'complectation' => $this->faker->paragraph(),
            'technical_characteristics' => $this->faker->paragraph(),
            'main_info' => $this->faker->paragraph(),
            'additional_info' => $this->faker->paragraph(),
            'adv_price' => $this->faker->numberBetween(10000, 1000000),
            'adv_price_comment' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'draft', 'inactive']),
            'check_data' => null,
            'loading_data' => null,
            'removal_data' => null,
        ];
    }
}
