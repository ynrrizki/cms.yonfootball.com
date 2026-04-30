<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'code',
    'price_flat',
    'price_percentage',
    'effective_date',
    'ended_date',
    'usage_limit',
    'usage_count',
    'is_active',
])]
class Voucher extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_flat' => 'integer',
            'price_percentage' => 'decimal:2',
            'effective_date' => 'datetime',
            'ended_date' => 'datetime',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected $attributes = [
        'usage_count' => 0,
        'is_active' => true,
    ];
}
