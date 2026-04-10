<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Products;
use App\Models\Suppliers;
use App\Models\Warehouse;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la compra')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->options(Suppliers::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Select::make('warehouse_id')
                            ->label('Bodega de recepción')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Productos comprados')
                    ->schema([
                        Repeater::make('items')
                            ->label('Ítems')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Producto')
                                    ->options(Products::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
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
                                TextInput::make('notes')
                                    ->label('Observaciones')
                                    ->nullable(),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->addActionLabel('Agregar producto')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
