<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brands;
use App\Models\Categories;
use App\Models\Suppliers;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->required(),
                                TextInput::make('barcode')
                                    ->label('Código de barras'),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        TextInput::make('cabys_code')
                            ->label('Código CABYS')
                            ->required(),
                        Grid::make(3)
                            ->schema([
                                Select::make('category_id')
                                    ->label('Categoría')
                                    ->options(Categories::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                                Select::make('supplier_id')
                                    ->label('Proveedor')
                                    ->options(Suppliers::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                                Select::make('brand_id')
                                    ->label('Marca')
                                    ->options(Brands::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Precios e impuestos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('purchase_price')
                                    ->label('Precio de compra')
                                    ->numeric()
                                    ->prefix('₡'),
                                TextInput::make('cost_price')
                                    ->label('Precio de costo')
                                    ->numeric()
                                    ->prefix('₡'),
                                TextInput::make('distributor_price')
                                    ->label('Precio distribuidor')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₡'),
                                TextInput::make('sale_price')
                                    ->label('Precio de venta')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₡'),
                                Select::make('tax_percentage')
                                    ->label('IVA (%)')
                                    ->options([
                                        0 => '0%',
                                        1 => '1%',
                                        2 => '2%',
                                        4 => '4%',
                                        13 => '13%',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Section::make('Control de inventario')
                    ->schema([
                        TextInput::make('min_stock')
                            ->label('Stock mínimo')
                            ->required()
                            ->numeric()
                            ->default(3),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),

                Section::make('Variantes')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship()
                            ->label('Variantes del producto')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->placeholder('Ej: Tanque Rojo'),
                                TextInput::make('sku')
                                    ->label('SKU variante')
                                    ->required()
                                    ->unique('product_variants', 'sku', ignoreRecord: true),
                                TextInput::make('barcode')
                                    ->label('Código de barras'),
                                KeyValue::make('attributes')
                                    ->label('Atributos')
                                    ->keyLabel('Atributo')
                                    ->valueLabel('Valor')
                                    ->addActionLabel('Agregar atributo')
                                    ->columnSpan(2),
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar variante')
                            ->collapsible(),
                    ])
                    ->collapsible(),

                Section::make('Compatibilidad de vehículo')
                    ->schema([
                        Repeater::make('vehicle_compatibility')
                            ->label('Compatibilidades')
                            ->schema([
                                TextInput::make('year')
                                    ->label('Año'),
                                TextInput::make('make')
                                    ->label('Marca vehículo'),
                                TextInput::make('model')
                                    ->label('Modelo'),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar compatibilidad'),
                    ]),
            ]);
    }
}
