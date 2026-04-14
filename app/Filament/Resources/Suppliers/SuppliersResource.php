<?php

namespace App\Filament\Resources\Suppliers;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Suppliers\Pages\CreateSuppliers;
use App\Filament\Resources\Suppliers\Pages\EditSuppliers;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Schemas\SuppliersForm;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Suppliers;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SuppliersResource extends Resource
{
    protected static ?string $model = Suppliers::class;

    protected static ?string $label = 'Proveedores';

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Catalogo;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SuppliersForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSuppliers::route('/create'),
            'edit' => EditSuppliers::route('/{record}/edit'),
        ];
    }
}
