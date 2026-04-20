<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'province_id',
        'canton_id',
        'district_id',
        'neighborhood_id',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function canton(): BelongsTo
    {
        return $this->belongsTo(Canton::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            $last = static::withoutGlobalScopes()->orderByDesc('id')->value('code');
            $number = $last ? (int) str_replace('KOB-', '', $last) + 1 : 1;
            $client->code = 'KOB-'.str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }
}
