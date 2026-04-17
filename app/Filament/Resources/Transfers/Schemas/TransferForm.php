<?php

namespace App\Filament\Resources\Transfers\Schemas;

use App\Models\Products;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Información del traslado')
                    ->aside()
                    ->schema([
                        Select::make('from_warehouse_id')
                            ->label('Bodega origen')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('to_warehouse_id')
                            ->label('Bodega destino')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->rules([
                                fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    if ($value && $value == $get('from_warehouse_id')) {
                                        $fail('La bodega destino no puede ser igual a la bodega origen.');
                                    }
                                },
                            ]),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Productos a trasladar')
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
                                        ->getComponent('transferVariantField')
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
                                                        ->mapWithKeys(fn (ProductVariant $v) => [$v->id => $v->name ?? $v->sku ?? "Variante #{$v->id}"])
                                                )
                                                ->searchable()
                                                ->required(),
                                        ]
                                        : [])
                                    ->key('transferVariantField'),
                                TextInput::make('quantity_requested')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0.0001)
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
