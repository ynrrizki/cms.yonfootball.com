<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'name',
    'leading_url',
    'price_original',
    'price',
    'price_discount',
    'is_active',
])]
class ProductVariant extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_original' => 'integer',
            'price' => 'integer',
            'price_discount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected $attributes = [
        'is_active' => true,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
