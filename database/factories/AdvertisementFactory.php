<?php

namespace Database\Factories;

use App\Models\Advertisement;
use App\Models\AdvertisementStatus;
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
        $statuses = AdvertisementStatus::all();
        $randomStatus = $statuses->random();

        return [
            'product_id' => Product::factory(),
            'title' => $this->faker->sentence(3),
            'category_id' => ProductCategories::factory(),
            'main_characteristics' => $this->faker->paragraph(),
            'complectation' => $this->faker->paragraph(),
            'technical_characteristics' => $this->faker->paragraph(),
            'main_info' => $this->faker->paragraph(),
            'additional_info' => $this->faker->paragraph(),
            'check_data' => [
                'status_id' => null,
                'comment' => $this->faker->optional()->sentence(),
            ],
            'loading_data' => [
                'status_id' => null,
                'comment' => $this->faker->optional()->sentence(),
            ],
            'removal_data' => [
                'status_id' => null,
                'comment' => $this->faker->optional()->sentence(),
            ],
            'adv_price' => $this->faker->optional()->numberBetween(10000, 1000000),
            'adv_price_comment' => $this->faker->optional()->sentence(),
            'main_img' => null,
            'status_id' => $randomStatus->id,
            'created_by' => User::factory(),
            'published_at' => $randomStatus->is_published ? $this->faker->optional()->dateTimeBetween('-1 year', 'now') : null,
        ];
    }
}
