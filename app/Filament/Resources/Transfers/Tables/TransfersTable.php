<?php

namespace App\Filament\Resources\Transfers\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable(),
                TextColumn::make('fromWarehouse.name')
                    ->label('Bodega origen')
                    ->sortable(),
                TextColumn::make('toWarehouse.name')
                    ->label('Bodega destino')
                    ->sortable(),
                TextColumn::make('requestedByUser.name')
                    ->label('Solicitado por'),
                TextColumn::make('requested_at')
                    ->label('Fecha solicitud')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'dispatched' => 'Despachado',
                        'received' => 'Recibido',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'dispatched' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'dispatched' => 'Despachado',
                        'received' => 'Recibido',
                        'cancelled' => 'Cancelado',
                    ]),
                SelectFilter::make('from_warehouse_id')
                    ->label('Bodega origen')
                    ->relationship('fromWarehouse', 'name'),
                SelectFilter::make('to_warehouse_id')
                    ->label('Bodega destino')
                    ->relationship('toWarehouse', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
