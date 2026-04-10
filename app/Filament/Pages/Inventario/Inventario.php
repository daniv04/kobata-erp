<?php

namespace App\Filament\Pages\Inventario;

use App\Enums\NavigationGroup;
use Filament\Pages\Page;
use UnitEnum;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use App\Models\WarehouseStock;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Warehouse;



class Inventario extends Page implements HasTable
{

    use InteractsWithTable;

    protected string $view = 'filament.pages.inventario.inventario';

    protected static ?string $title = 'Inventario';

    protected static ?string $navigationLabel = 'Inventario';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::BodegasInventario;

    protected static ?int $navigationSort = 2;


    public function table(Table $table): Table
    {
        return $table
            ->query(
                WarehouseStock::query()->with(['product.category', 'warehouse'])
            )
            ->columns([
                TextColumn::make('product.sku')
                ->label('SKU')
                ->searchable()
                ->sortable(),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable(),

                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('reserved_quantity')
                    ->label('Reservado')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('available')
                        ->label('Disponible')
                        ->numeric(decimalPlaces: 0)
                        ->getStateUsing(fn (WarehouseStock $record): float => $record->quantity - $record->reserved_quantity)
                        ->color(fn (float $state): string => $state <= 0 ? 'danger' : 'success'),
                ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Bodega')
                    ->relationship('warehouse', 'name'),

                SelectFilter::make('product.category_id')
                    ->label('Categoría')
                    ->relationship('product.category', 'name'),
                ]
            )
            ->defaultSort('product.name');

    }
}
