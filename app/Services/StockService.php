<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    /**
     * Apply a stock movement and record it in the audit log.
     *
     * @param  float  $quantity  positive = entrada, negative = salida
     * @param  string|null  $referenceType  'transfer' | 'adjustment' | null
     */
    public function adjust(
        int $productId,
        int $warehouseId,
        float $quantity,
        StockMovementType $type,
        ?string $referenceType,
        ?int $referenceId,
        float $unitCost,
        ?string $notes,
        int $userId,
        ?int $variantId = null,
    ): StockMovement {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $type, $referenceType, $referenceId, $unitCost, $notes, $userId, $variantId): StockMovement {
            /** @var WarehouseStock $stock */
            $stock = WarehouseStock::lockForUpdate()
                ->firstOrCreate(
                    [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'updated_at' => now(),
                    ],
                );

            $quantityBefore = (float) $stock->quantity;
            $quantityAfter = $quantityBefore + $quantity;
            $availableQuantity = $quantityBefore - (float) $stock->reserved_quantity;

            if ($quantity < 0 && ($availableQuantity + $quantity) < 0) {
                throw new RuntimeException("Stock insuficiente: disponible {$availableQuantity}, solicitado ".abs($quantity));
            }

            $stock->quantity = $quantityAfter;
            $stock->updated_at = now();
            $stock->save();

            return StockMovement::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'type' => $type->value,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'unit_cost' => $unitCost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => $userId,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Increment the reserved quantity for a product in a warehouse.
     */
    public function reserve(int $productId, int $warehouseId, float $quantity, ?int $variantId = null): void
    {
        DB::transaction(function () use ($productId, $warehouseId, $quantity, $variantId): void {
            /** @var WarehouseStock $stock */
            $stock = WarehouseStock::lockForUpdate()
                ->firstOrCreate(
                    [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'updated_at' => now(),
                    ],
                );

            $availableQuantity = (float) $stock->quantity - (float) $stock->reserved_quantity;

            if ($availableQuantity < $quantity) {
                throw new RuntimeException("Stock insuficiente para reservar: disponible {$availableQuantity}, solicitado {$quantity}");
            }

            $stock->reserved_quantity = (float) $stock->reserved_quantity + $quantity;
            $stock->updated_at = now();
            $stock->save();
        });
    }

    /**
     * Decrement the reserved quantity for a product in a warehouse.
     */
    public function releaseReservation(int $productId, int $warehouseId, float $quantity, ?int $variantId = null): void
    {
        DB::transaction(function () use ($productId, $warehouseId, $quantity, $variantId): void {
            /** @var WarehouseStock $stock */
            $stock = WarehouseStock::lockForUpdate()
                ->firstOrCreate(
                    [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'updated_at' => now(),
                    ],
                );

            $newReserved = (float) $stock->reserved_quantity - $quantity;

            if ($newReserved < 0) {
                $newReserved = 0;
            }

            $stock->reserved_quantity = $newReserved;
            $stock->updated_at = now();
            $stock->save();
        });
    }
}
