<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticketNumber = 'TK-' . fake()->unique()->numerify('######');

        return [
            'ticket_number' => $ticketNumber,
            'product_variant_id' => ProductVariant::factory(),
            'status' => OrderStatus::PENDING,
            'user_inputs' => [],
            'processed_by' => User::factory(),
            'completed_at' => null,
            'notes' => null,
        ];
    }
}
