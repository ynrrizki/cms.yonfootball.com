<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priceOriginal = fake()->numberBetween(1000, 50000);
        $price = $priceOriginal + fake()->numberBetween(100, 5000);

        return [
            'product_id' => Product::factory(),
            'name' => fake()->words(2, true),
            'leading_url' => null,
            'price_original' => $priceOriginal,
            'price' => $price,
            'price_discount' => fake()->numberBetween(0, max(0, $price - 1)),
            'is_active' => true,
        ];
    }
}
