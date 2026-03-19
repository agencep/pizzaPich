<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'base',
        'baseLabel',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];
}
