<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    protected $fillable = [
        'transfer_id',
        'product_id',
        'quantity_requested',
        'quantity_dispatched',
        'quantity_received',
        'unit_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:4',
            'quantity_dispatched' => 'decimal:4',
            'quantity_received' => 'decimal:4',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }
}
