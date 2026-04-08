<?php

namespace App\Filament\Resources\Transfers\Pages;

use App\Filament\Resources\Transfers\TransferResource;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Services\StockService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return DB::transaction(function () use ($data, $items): Transfer {
            $year = now()->year;
            $count = Transfer::whereYear('created_at', $year)->count() + 1;
            $referenceNumber = 'TRF-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);

            /** @var Transfer $transfer */
            $transfer = Transfer::create([
                ...$data,
                'reference_number' => $referenceNumber,
                'requested_by_user_id' => auth()->id(),
                'requested_at' => now(),
                'status' => 'pending',
            ]);

            $stockService = app(StockService::class);

            foreach ($items as $item) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'unit_cost' => $item['unit_cost'],
                    'notes' => $item['notes'] ?? null,
                ]);

                $stockService->reserve(
                    productId: (int) $item['product_id'],
                    warehouseId: $transfer->from_warehouse_id,
                    quantity: (float) $item['quantity_requested'],
                );
            }

            return $transfer;
        });
    }
}
