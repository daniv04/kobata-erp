<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return DB::transaction(function () use ($data, $items): Purchase {
            $year = now()->year;
            $count = Purchase::whereYear('created_at', $year)->count() + 1;
            $referenceNumber = 'PUR-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);

            /** @var Purchase $purchase */
            $purchase = Purchase::create([
                ...$data,
                'reference_number' => $referenceNumber,
                'user_id' => auth()->id(),
                'ordered_at' => now(),
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $purchase;
        });
    }
}
