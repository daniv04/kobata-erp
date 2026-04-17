<?php

namespace App\Filament\Resources\Transfers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Productos del traslado';

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
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto'),
                TextColumn::make('variant.name')
                    ->label('Variante')
                    ->placeholder('—'),
                TextColumn::make('quantity_requested')
                    ->label('Cantidad solicitada')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('quantity_dispatched')
                    ->label('Cantidad despachada')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('quantity_received')
                    ->label('Cantidad recibida')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('notes')
                    ->label('Observaciones'),
            ]);
    }
}
