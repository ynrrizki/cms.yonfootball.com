<?php

namespace App\Models;

use App\Enums\AuditType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_agent',
    'resource_type',
    'resource_id',
    'resource_snapshot',
    'type',
    'users_id',
])]
class Audit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'resource_snapshot' => 'array',
            'type' => AuditType::class,
        ];
    }

    protected $attributes = [
        'resource_snapshot' => '[]',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
