<?php

namespace App\Filament\Resources\Products;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Products\Pages\CreateProducts;
use App\Filament\Resources\Products\Pages\EditProducts;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProducts;
use App\Filament\Resources\Products\RelationManagers\WarehouseStocksRelationManager;
use App\Filament\Resources\Products\Schemas\ProductsForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Products;
use BackedEnum;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ProductsResource extends Resource
{
    protected static ?string $model = Products::class;

    protected static ?string $label = 'Productos';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Catalogo;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sku')
                                    ->label('SKU'),
                                TextEntry::make('barcode')
                                    ->label('Código de barras'),
                                TextEntry::make('name')
                                    ->label('Nombre'),
                            ]),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        TextEntry::make('cabys_code')
                            ->label('Código CABYS'),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('category.name')
                                    ->label('Categoría'),
                                TextEntry::make('supplier.name')
                                    ->label('Proveedor'),
                                TextEntry::make('brand.name')
                                    ->label('Marca'),
                            ]),
                    ]),

                Section::make('Precios e impuestos')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('purchase_price')
                                    ->label('Precio de compra')
                                    ->money(),
                                TextEntry::make('cost_price')
                                    ->label('Precio de costo')
                                    ->money(),
                                TextEntry::make('distributor_price')
                                    ->label('Precio distribuidor')
                                    ->money(),
                                TextEntry::make('sale_price')
                                    ->label('Precio de venta')
                                    ->money(),
                                TextEntry::make('tax_percentage')
                                    ->label('IVA (%)'),
                            ]),
                    ]),

                Section::make('Control de inventario')
                    ->schema([
                        TextEntry::make('min_stock')
                            ->label('Stock mínimo'),
                        TextEntry::make('is_active')
                            ->label('Activo')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ]),

                Section::make('Variantes')
                    ->schema([
                        RepeatableEntry::make('variants')
                            ->label('')
                            ->schema([
                                TextEntry::make('sku')
                                    ->label('SKU'),
                                TextEntry::make('barcode')
                                    ->label('Código de barras'),
                                TextEntry::make('is_active')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activa' : 'Inactiva')
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                                KeyValueEntry::make('attributes')
                                    ->label('Atributos')
                                    ->columnSpan(2),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn ($record): bool => $record->variants()->exists())
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            WarehouseStocksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProducts::route('/create'),
            'view' => ViewProducts::route('/{record}'),
            'edit' => EditProducts::route('/{record}/edit'),
        ];
    }
}
