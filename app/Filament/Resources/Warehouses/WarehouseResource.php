<?php

namespace App\Filament\Resources\Warehouses;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Warehouses\Pages\CreateWarehouse;
use App\Filament\Resources\Warehouses\Pages\EditWarehouse;
use App\Filament\Resources\Warehouses\Pages\ListWarehouses;
use App\Filament\Resources\Warehouses\Pages\ViewWarehouse;
use App\Filament\Resources\Warehouses\RelationManagers\WarehouseStocksRelationManager;
use App\Filament\Resources\Warehouses\Schemas\WarehouseForm;
use App\Filament\Resources\Warehouses\Tables\WarehousesTable;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $label = 'Bodega';

    protected static ?string $pluralLabel = 'Bodegas';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::BodegasInventario;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la bodega')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('responsibleUser.name')
                            ->label('Responsable'),
                        TextEntry::make('phone')
                            ->label('Teléfono'),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ]),

                Section::make('Ubicación')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Dirección')
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('province')
                                    ->label('Provincia'),
                                TextEntry::make('canton')
                                    ->label('Cantón'),
                                TextEntry::make('district')
                                    ->label('Distrito'),
                            ]),
                    ]),

                Section::make('Estado')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Activo')
                            ->boolean(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view' => ViewWarehouse::route('/{record}'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }
}
