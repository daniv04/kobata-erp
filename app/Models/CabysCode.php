<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabysCode extends Model
{
    protected $fillable = [
        'code',
        'description',
        'tax_percentage',
        'category',
        'is_active',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }
}
