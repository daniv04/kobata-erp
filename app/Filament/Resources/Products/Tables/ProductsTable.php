<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\StockMovementType;
use App\Models\Brands;
use App\Models\Categories;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label('Marca')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Precio de venta')
                    ->money()
                    ->sortable(),
                TextColumn::make('min_stock')
                    ->label('Stock mínimo')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('barcode')
                    ->label('Código de barras')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cabys_code')
                    ->label('CABYS')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_price')
                    ->label('Precio de compra')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label('Precio de costo')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('distributor_price')
                    ->label('Precio distribuidor')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->options(Categories::where('is_active', true)->pluck('name', 'id')),
                SelectFilter::make('brand_id')
                    ->label('Marca')
                    ->options(Brands::where('is_active', true)->pluck('name', 'id')),
                SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('initialStock')
                    ->label('Cargar stock inicial')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->hidden(function ($record): bool {
                        $activeVariants = $record->variants()->where('is_active', true)->pluck('id');

                        if ($activeVariants->isEmpty()) {
                            return StockMovement::where('product_id', $record->id)
                                ->where('type', StockMovementType::InitialStock->value)
                                ->whereNull('variant_id')
                                ->exists();
                        }

                        $variantsWithStock = StockMovement::where('product_id', $record->id)
                            ->where('type', StockMovementType::InitialStock->value)
                            ->whereIn('variant_id', $activeVariants)
                            ->distinct('variant_id')
                            ->count('variant_id');

                        return $variantsWithStock >= $activeVariants->count();
                    })
                    ->schema(fn ($record) => [
                        Select::make('warehouse_id')
                            ->label('Bodega')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('variant_id')
                            ->label('Variante')
                            ->options(function () use ($record) {
                                $loadedVariantIds = StockMovement::where('product_id', $record->id)
                                    ->where('type', StockMovementType::InitialStock->value)
                                    ->whereNotNull('variant_id')
                                    ->pluck('variant_id');

                                return ProductVariant::where('product_id', $record->id)
                                    ->where('is_active', true)
                                    ->whereNotIn('id', $loadedVariantIds)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->visible(fn (): bool => $record->variants()->where('is_active', true)->exists()),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(0.0001)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->nullable(),
                    ])
                    ->action(function (array $data, $record): void {
                        $stockService = app(StockService::class);

                        try {
                            $stockService->adjust(
                                productId: $record->id,
                                warehouseId: (int) $data['warehouse_id'],
                                quantity: (float) $data['quantity'],
                                type: StockMovementType::InitialStock,
                                referenceType: 'adjustment',
                                referenceId: null,
                                unitCost: 0.0,
                                notes: $data['notes'] ?? null,
                                userId: auth()->id(),
                                variantId: isset($data['variant_id']) ? (int) $data['variant_id'] : null,
                            );

                            Notification::make()
                                ->title('Stock inicial cargado')
                                ->body("Se registraron {$data['quantity']} unidades en la bodega seleccionada.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Error al cargar stock')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([

            ]);
    }
}
