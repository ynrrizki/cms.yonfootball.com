<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_number',
    'order_id',
    'voucher_id',
    'customer_name',
    'customer_phone',
    'customer_email',
    'status',
    'product_name',
    'product_snapshot',
    'payment_url',
    'payment_method',
    'payment_snapshot',
    'paid_at',
])]
class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'product_snapshot' => 'array',
            'payment_snapshot' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    protected $attributes = [
        'status' => TransactionStatus::PENDING,
        'product_snapshot' => '[]',
        'payment_snapshot' => '[]',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
