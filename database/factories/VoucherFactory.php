<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('VCHR-####')),
            'price_flat' => fake()->numberBetween(1000, 50000),
            'price_percentage' => fake()->randomFloat(2, 0, 100),
            'effective_date' => now(),
            'ended_date' => now()->addMonth(),
            'usage_limit' => fake()->optional()->numberBetween(1, 100),
            'usage_count' => 0,
            'is_active' => true,
        ];
    }
}
