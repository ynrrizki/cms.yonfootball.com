<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'cover_url', 'link_url', 'sort_order'])]
class Banner extends Model
{
    use HasFactory;

    protected $attributes = [
        'sort_order' => 0,
    ];
}
