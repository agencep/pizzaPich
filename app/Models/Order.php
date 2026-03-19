<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'total',
        'received',
        'change',
        'items',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
        'received' => 'float',
        'change' => 'float',
    ];
}
