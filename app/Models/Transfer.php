<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    protected $fillable = [
        'reference_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'requested_by_user_id',
        'dispatched_by_user_id',
        'received_by_user_id',
        'status',
        'notes',
        'requested_at',
        'dispatched_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function dispatchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_user_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', 'transfer');
    }
}
