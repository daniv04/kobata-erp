<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'hacienda_name',
        'contact_name',
        'id_number_type',
        'id_number',
        'economic_activity_code',
        'economic_activity_description',
        'email',
        'phone',
        'province',
        'canton',
        'district',
        'neighborhood',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
