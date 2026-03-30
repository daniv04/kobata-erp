<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brands;
use App\Models\Categories;
use App\Models\Suppliers;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('barcode'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('cabys_code')
                    ->required(),
                TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->default(3),
                TextInput::make('purchase_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('distributor_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('sale_price')
                    ->required()
                    ->numeric()
                    ->suffix('%'),
                Grid::make(3)
                ->columnSpanFull() 
                    ->schema([
                        Select::make('category_id')
                            ->label('Categoría')
                            ->options(Categories::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->options(Suppliers::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('brand_id')
                            ->label('Marca')
                            ->options(Brands::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ]),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
