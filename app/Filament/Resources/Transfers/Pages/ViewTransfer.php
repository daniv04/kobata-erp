<?php

namespace App\Filament\Resources\Transfers\Pages;

use App\Enums\StockMovementType;
use App\Filament\Resources\Transfers\TransferResource;
use App\Models\Transfer;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewTransfer extends ViewRecord
{
    protected static string $resource = TransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dispatch')
                ->label('Despachar')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->fillForm(function (): array {
                    return [
                        'dispatch_items' => $this->record->items->map(fn ($item) => [
                            'transfer_item_id' => $item->id,
                            'product_name' => $item->product->name,
                            'quantity_requested' => $item->quantity_requested,
                            'quantity_dispatched' => $item->quantity_requested,
                        ])->toArray(),
                    ];
                })
                ->schema([
                    Repeater::make('dispatch_items')
                        ->label('Ítems a despachar')
                        ->schema([
                            TextInput::make('product_name')
                                ->label('Producto')
                                ->disabled(),
                            TextInput::make('quantity_requested')
                                ->label('Cantidad solicitada')
                                ->disabled()
                                ->numeric(),
                            TextInput::make('quantity_dispatched')
                                ->label('Cantidad a despachar')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(3)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])
                ->modalHeading('Despachar traslado')
                ->modalSubmitActionLabel('Confirmar despacho')
                ->action(function (array $data): void {
                    /** @var Transfer $transfer */
                    $transfer = $this->record;
                    $stockService = app(StockService::class);

                    DB::transaction(function () use ($transfer, $data, $stockService): void {
                        $items = $transfer->items()->with('product')->get();

                        foreach ($data['dispatch_items'] as $dispatchData) {
                            $item = $items->firstWhere('id', $dispatchData['transfer_item_id']);

                            if (! $item) {
                                continue;
                            }

                            $qtyDispatched = (float) $dispatchData['quantity_dispatched'];
                            $item->update(['quantity_dispatched' => $qtyDispatched]);

                            $stockService->releaseReservation(
                                productId: $item->product_id,
                                warehouseId: $transfer->from_warehouse_id,
                                quantity: (float) $item->quantity_requested,
                            );

                            $stockService->adjust(
                                productId: $item->product_id,
                                warehouseId: $transfer->from_warehouse_id,
                                quantity: -$qtyDispatched,
                                type: StockMovementType::TransferOut,
                                referenceType: 'transfer',
                                referenceId: $transfer->id,
                                unitCost: (float) $item->unit_cost,
                                notes: $item->notes,
                                userId: auth()->id(),
                            );
                        }

                        $transfer->update([
                            'status' => 'dispatched',
                            'dispatched_by_user_id' => auth()->id(),
                            'dispatched_at' => now(),
                        ]);
                    });

                    Notification::make()
                        ->title('Traslado despachado')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->record->getRouteKey()]));
                }),

            Action::make('receive')
                ->label('Confirmar recepción')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'dispatched')
                ->fillForm(function (): array {
                    return [
                        'receive_items' => $this->record->items->map(fn ($item) => [
                            'transfer_item_id' => $item->id,
                            'product_name' => $item->product->name,
                            'quantity_dispatched' => $item->quantity_dispatched,
                            'quantity_received' => $item->quantity_dispatched,
                        ])->toArray(),
                    ];
                })
                ->schema([
                    Repeater::make('receive_items')
                        ->label('Ítems recibidos')
                        ->schema([
                            TextInput::make('product_name')
                                ->label('Producto')
                                ->disabled(),
                            TextInput::make('quantity_dispatched')
                                ->label('Cantidad despachada')
                                ->disabled()
                                ->numeric(),
                            TextInput::make('quantity_received')
                                ->label('Cantidad recibida')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(3)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])
                ->modalHeading('Confirmar recepción')
                ->modalSubmitActionLabel('Confirmar recepción')
                ->action(function (array $data): void {
                    /** @var Transfer $transfer */
                    $transfer = $this->record;
                    $stockService = app(StockService::class);

                    DB::transaction(function () use ($transfer, $data, $stockService): void {
                        $items = $transfer->items()->with('product')->get();

                        foreach ($data['receive_items'] as $receiveData) {
                            $item = $items->firstWhere('id', $receiveData['transfer_item_id']);

                            if (! $item) {
                                continue;
                            }

                            $qtyReceived = (float) $receiveData['quantity_received'];
                            $item->update(['quantity_received' => $qtyReceived]);

                            $stockService->adjust(
                                productId: $item->product_id,
                                warehouseId: $transfer->to_warehouse_id,
                                quantity: $qtyReceived,
                                type: StockMovementType::TransferIn,
                                referenceType: 'transfer',
                                referenceId: $transfer->id,
                                unitCost: (float) $item->unit_cost,
                                notes: $item->notes,
                                userId: auth()->id(),
                            );
                        }

                        $transfer->update([
                            'status' => 'received',
                            'received_by_user_id' => auth()->id(),
                            'received_at' => now(),
                        ]);
                    });

                    Notification::make()
                        ->title('Recepción confirmada')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->record->getRouteKey()]));
                }),

            Action::make('cancel')
                ->label('Cancelar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Cancelar traslado')
                ->modalDescription('¿Estás seguro? Esta acción liberará las reservas y no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, cancelar traslado')
                ->action(function (): void {
                    /** @var Transfer $transfer */
                    $transfer = $this->record;
                    $stockService = app(StockService::class);

                    DB::transaction(function () use ($transfer, $stockService): void {
                        foreach ($transfer->items as $item) {
                            $stockService->releaseReservation(
                                productId: $item->product_id,
                                warehouseId: $transfer->from_warehouse_id,
                                quantity: (float) $item->quantity_requested,
                            );
                        }

                        $transfer->update(['status' => 'cancelled']);
                    });

                    Notification::make()
                        ->title('Traslado cancelado')
                        ->warning()
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->record->getRouteKey()]));
                }),
        ];
    }
}
