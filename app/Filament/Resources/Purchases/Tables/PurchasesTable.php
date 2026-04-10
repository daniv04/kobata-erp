<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Enums\PurchaseStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->default('Sin proveedor')
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Bodega')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Registrado por'),
                TextColumn::make('ordered_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PurchaseStatus::class),
                SelectFilter::make('warehouse_id')
                    ->label('Bodega')
                    ->relationship('warehouse', 'name'),
                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
