<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyStatuses;
use App\Models\Regions;
use App\Models\Sources;
use App\Models\User;
use App\Models\Warehouses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'name' => $this->faker->company(),
            'inn' => $this->faker->numerify('##########'),
            'source_id' => Sources::factory(),
            'region_id' => Regions::factory(),
            'regional_user_id' => User::factory(),
            'owner_user_id' => User::factory(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'site' => $this->faker->url(),
            'common_info' => $this->faker->paragraph(),
            'company_status_id' => CompanyStatuses::factory(),
        ];
    }
}
