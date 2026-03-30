<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\CreateSuppliers;
use App\Filament\Resources\Suppliers\Pages\EditSuppliers;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Schemas\SuppliersForm;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Suppliers;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SuppliersResource extends Resource
{
    protected static ?string $model = Suppliers::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
