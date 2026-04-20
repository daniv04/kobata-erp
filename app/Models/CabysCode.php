<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabysCode extends Model
{
    protected $fillable = ['code', 'description', 'tax_percentage'];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
        ];
    }
}
