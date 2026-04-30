<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'code',
    'leading_url',
    'background_url',
    'category_id',
    'inputs',
    'is_active',
    'is_popular',
])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }

    protected $attributes = [
        'inputs' => '[]',
        'is_active' => true,
        'is_popular' => false,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
