<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
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
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => fake()->unique()->bothify('PRD-####'),
            'leading_url' => null,
            'background_url' => null,
            'category_id' => ProductCategory::factory(),
            'inputs' => [],
            'is_active' => true,
            'is_popular' => false,
        ];
    }
}
