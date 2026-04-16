<?php

namespace App\Filament\Resources\Warehouses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WarehouseStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouseStocks';

    protected static ?string $title = 'Stock actual';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('product')->where('quantity', '>', 0))
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU'),
                TextColumn::make('product.name')
                    ->label('Producto'),
                TextColumn::make('variant.name')
                    ->label('Variante')
                    ->placeholder('Sin variante'),
                TextColumn::make('quantity')
                    ->label('Cantidad disponible')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('reserved_quantity')
                    ->label('Cantidad reservada')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
