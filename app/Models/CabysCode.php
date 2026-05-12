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

    public static function isService(string $code): bool
    {
        return in_array($code[0] ?? '', ['5', '6', '7', '8']);
    }
}
