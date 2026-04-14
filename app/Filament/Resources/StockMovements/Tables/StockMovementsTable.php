<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Enums\StockMovementType;
use App\Models\Products;
use App\Models\Warehouse;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label('SKU'),
                TextColumn::make('variant.name')
                    ->label('Variante')
                    ->placeholder('—'),
                TextColumn::make('warehouse.name')
                    ->label('Bodega'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StockMovementType::from($state)->getLabel())
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'transfer_in' => 'info',
                        'transfer_out' => 'warning',
                        'adjustment' => 'gray',
                        'sale_out' => 'danger',
                        'consignment_out' => 'purple',
                        'sale_return' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2)
                    ->color(fn ($record): string => $record->quantity >= 0 ? 'success' : 'danger'),
                TextColumn::make('quantity_before')
                    ->label('Stock anterior')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity_after')
                    ->label('Stock resultante')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('unit_cost')
                    ->label('Costo unitario')
                    ->money()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Usuario'),
                TextColumn::make('reference')
                    ->label('Referencia')
                    ->state(fn ($record): string => $record->reference_type
                        ? "{$record->reference_type} #{$record->reference_id}"
                        : '—'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(StockMovementType::class),
                SelectFilter::make('warehouse_id')
                    ->label('Bodega')
                    ->options(Warehouse::pluck('name', 'id')),
                SelectFilter::make('product_id')
                    ->label('Producto')
                    ->options(Products::pluck('name', 'id'))
                    ->searchable(),
                Filter::make('date_from')
                    ->label('Desde')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Desde'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['date_from'],
                        fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                    )),
                Filter::make('date_until')
                    ->label('Hasta')
                    ->form([
                        DatePicker::make('date_until')
                            ->label('Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['date_until'],
                        fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                    )),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
