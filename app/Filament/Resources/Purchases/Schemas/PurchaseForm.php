<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Products;
use App\Models\ProductVariant;
use App\Models\Suppliers;
use App\Models\Warehouse;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Select $component) => $component
                                        ->getContainer()
                                        ->getComponent('purchaseVariantField')
                                        ->getChildSchema()
                                        ->fill()),
                                Grid::make(1)
                                    ->schema(fn (Get $get): array => $get('product_id') && ProductVariant::where('product_id', $get('product_id'))->where('is_active', true)->exists()
                                        ? [
                                            Select::make('variant_id')
                                                ->label('Variante')
                                                ->options(
                                                    ProductVariant::where('product_id', $get('product_id'))
                                                        ->where('is_active', true)
                                                        ->get()
                                                        ->mapWithKeys(fn (ProductVariant $v) => [$v->id => $v->name ?? $v->sku])
                                                )
                                                ->searchable()
                                                ->required(),
                                        ]
                                        : [])
                                    ->key('purchaseVariantField'),
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
                            ->columns(5)
                            ->minItems(1)
                            ->addActionLabel('Agregar producto')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
