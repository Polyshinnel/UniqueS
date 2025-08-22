<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\ProductStatus;
use App\Models\User;
use App\Models\Warehouses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();
        
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category_id' => ProductCategories::factory(),
            'company_id' => $company,
            'owner_id' => $company->owner_user_id, // Владелец товара - владелец компании
            'regional_id' => User::factory(),
            'status_id' => ProductStatus::factory(),
            'warehouse_id' => Warehouses::factory(),
            'common_commentary' => $this->faker->optional()->paragraph(),
        ];
    }
}
