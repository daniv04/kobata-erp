<?php

namespace App\Filament\Resources\Purchases\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Productos de la compra';

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
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto'),
                TextColumn::make('variant.name')
                    ->label('Variante')
                    ->placeholder('—'),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('unit_cost')
                    ->label('Costo unitario')
                    ->money('CRC'),
                TextColumn::make('notes')
                    ->label('Observaciones')
                    ->placeholder('—'),
            ]);
    }
}
