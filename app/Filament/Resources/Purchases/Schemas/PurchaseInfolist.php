<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la compra')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->label('N° de referencia'),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                                TextEntry::make('ordered_at')
                                    ->label('Fecha de registro')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('supplier.name')
                                    ->label('Proveedor')
                                    ->default('Sin proveedor'),
                                TextEntry::make('warehouse.name')
                                    ->label('Bodega de recepción'),
                                TextEntry::make('user.name')
                                    ->label('Registrado por'),
                                TextEntry::make('received_at')
                                    ->label('Fecha de recepción')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Pendiente'),
                                TextEntry::make('notes')
                                    ->label('Notas')
                                    ->columnSpan(2)
                                    ->placeholder('Sin notas'),
                            ]),
                    ]),
            ]);
    }
}
