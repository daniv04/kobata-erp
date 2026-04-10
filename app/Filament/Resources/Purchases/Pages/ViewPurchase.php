<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('receive')
                ->label('Confirmar recepción')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === PurchaseStatus::Pending)
                ->fillForm(function (): array {
                    return [
                        'receive_items' => $this->record->items->map(fn ($item) => [
                            'purchase_item_id' => $item->id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                        ])->toArray(),
                    ];
                })
                ->schema([
                    Repeater::make('receive_items')
                        ->label('Ítems a recibir')
                        ->schema([
                            TextInput::make('product_name')
                                ->label('Producto')
                                ->disabled(),
                            TextInput::make('quantity')
                                ->label('Cantidad')
                                ->disabled()
                                ->numeric(),
                        ])
                        ->columns(2)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])
                ->modalHeading('Confirmar recepción de compra')
                ->modalDescription('Esto ingresará el stock de los productos a la bodega indicada.')
                ->modalSubmitActionLabel('Confirmar recepción')
                ->action(function (array $data): void {
                    /** @var Purchase $purchase */
                    $purchase = $this->record;
                    $stockService = app(StockService::class);

                    DB::transaction(function () use ($purchase, $stockService): void {
                        $items = $purchase->items()->with('product')->get();

                        foreach ($items as $item) {
                            $stockService->adjust(
                                productId: $item->product_id,
                                warehouseId: $purchase->warehouse_id,
                                quantity: (float) $item->quantity,
                                type: StockMovementType::Purchase,
                                referenceType: 'purchase',
                                referenceId: $purchase->id,
                                unitCost: (float) $item->unit_cost,
                                notes: $item->notes,
                                userId: auth()->id(),
                            );
                        }

                        $purchase->update([
                            'status' => PurchaseStatus::Received,
                            'received_at' => now(),
                        ]);
                    });

                    Notification::make()
                        ->title('Compra recibida — stock actualizado')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->record->getRouteKey()]));
                }),

            Action::make('cancel')
                ->label('Cancelar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === PurchaseStatus::Pending)
                ->requiresConfirmation()
                ->modalHeading('Cancelar compra')
                ->modalDescription('¿Estás seguro? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, cancelar compra')
                ->action(function (): void {
                    $this->record->update(['status' => PurchaseStatus::Cancelled]);

                    Notification::make()
                        ->title('Compra cancelada')
                        ->warning()
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->record->getRouteKey()]));
                }),
        ];
    }
}
