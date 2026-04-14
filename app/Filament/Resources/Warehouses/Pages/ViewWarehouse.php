<?php

namespace App\Filament\Resources\Warehouses\Pages;

use App\Enums\StockMovementType;
use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Models\Products;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('adjustStock')
                ->label('Ajustar stock')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Select::make('product_id')
                        ->label('Producto')
                        ->options(Products::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Select $component) => $component
                            ->getContainer()
                            ->getComponent('variantFields')
                            ->getChildSchema()
                            ->fill()),
                    Grid::make(1)
                        ->schema(fn (Get $get): array => $get('product_id')
                            ? [
                                Select::make('variant_id')
                                    ->label('Variante')
                                    ->options(
                                        ProductVariant::where('product_id', $get('product_id'))
                                            ->where('is_active', true)
                                            ->pluck('sku', 'id')
                                    )
                                    ->searchable()
                                    ->visible(fn () => ProductVariant::where('product_id', $get('product_id'))
                                        ->where('is_active', true)
                                        ->exists()),
                            ]
                            : [])
                        ->key('variantFields'),
                    Select::make('movement_type')
                        ->label('Tipo de movimiento')
                        ->options([
                            'entrada' => 'Entrada de inventario',
                            'salida' => 'Salida / ajuste',
                        ])
                        ->required(),
                    TextInput::make('quantity')
                        ->label('Cantidad')
                        ->numeric()
                        ->minValue(0.0001)
                        ->required(),
                    TextInput::make('unit_cost')
                        ->label('Costo unitario')
                        ->numeric()
                        ->prefix('₡')
                        ->required(),
                    Textarea::make('notes')
                        ->label('Motivo del ajuste')
                        ->required(),
                ])
                ->action(function (array $data, Warehouse $record, StockService $stockService): void {
                    $quantity = (float) $data['quantity'];

                    if ($data['movement_type'] === 'salida') {
                        $quantity = -$quantity;
                    }

                    $movement = $stockService->adjust(
                        productId: (int) $data['product_id'],
                        warehouseId: $record->id,
                        quantity: $quantity,
                        type: StockMovementType::Adjustment,
                        referenceType: null,
                        referenceId: null,
                        unitCost: (float) $data['unit_cost'],
                        notes: $data['notes'],
                        userId: auth()->id(),
                        variantId: isset($data['variant_id']) ? (int) $data['variant_id'] : null,
                    );

                    Notification::make()
                        ->title('Stock ajustado')
                        ->body("Nuevo stock: {$movement->quantity_after}")
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl(['record' => $this->record->getRouteKey()]));
                }),

            EditAction::make(),
        ];
    }
}
