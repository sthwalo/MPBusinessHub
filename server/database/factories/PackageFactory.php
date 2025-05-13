<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Package::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Basic', 'Premium', 'Enterprise']),
            'description' => $this->faker->paragraph(),
            'monthly_price' => $this->faker->randomElement([0, 99, 199, 299]),
            'annual_price' => $this->faker->randomElement([0, 990, 1990, 2990]),
            'advert_limit' => $this->faker->randomElement([1, 5, 10, 20]),
            'product_limit' => $this->faker->randomElement([5, 20, 50, 100]),
            'features' => json_encode([
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence()
            ]),
            'is_active' => true,
            'popular' => $this->faker->boolean(20),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
