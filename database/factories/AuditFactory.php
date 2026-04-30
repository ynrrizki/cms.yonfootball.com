<?php

namespace Database\Factories;

use App\Enums\AuditType;
use App\Models\Audit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Audit>
 */
class AuditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_agent' => fake()->userAgent(),
            'resource_type' => fake()->randomElement(['Products', 'Product Variants', 'Orders', 'Vouchers', 'Transactions']),
            'resource_id' => fake()->randomNumber(),
            'resource_snapshot' => [],
            'type' => AuditType::INSERT,
            'users_id' => User::factory(),
        ];
    }
}
