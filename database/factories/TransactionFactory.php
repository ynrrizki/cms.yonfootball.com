<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoiceNumber = 'INV-' . fake()->unique()->numerify('######');

        return [
            'invoice_number' => $invoiceNumber,
            'order_id' => Order::factory(),
            'voucher_id' => Voucher::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_email' => fake()->safeEmail(),
            'status' => TransactionStatus::PENDING,
            'product_name' => fake()->words(2, true),
            'product_snapshot' => [],
            'payment_url' => fake()->url(),
            'payment_method' => fake()->randomElement(['QRIS', 'EWALLET', 'VA']),
            'payment_snapshot' => [],
            'paid_at' => null,
        ];
    }
}
