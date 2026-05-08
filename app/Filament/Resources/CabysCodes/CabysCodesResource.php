<?php

namespace App\Filament\Resources\CabysCodes;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CabysCodes\Pages\CreateCabysCode;
use App\Filament\Resources\CabysCodes\Pages\EditCabysCode;
use App\Filament\Resources\CabysCodes\Pages\ListCabysCodes;
use App\Filament\Resources\CabysCodes\Schemas\CabysCodeForm;
use App\Filament\Resources\CabysCodes\Tables\CabysCodesTable;
use App\Models\CabysCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CabysCodesResource extends Resource
{
    protected static ?string $model = CabysCode::class;

    protected static ?string $label = 'Código CABYS';

    protected static ?string $pluralLabel = 'Códigos CABYS';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Catalogo;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return CabysCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CabysCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCabysCodes::route('/'),
            'create' => CreateCabysCode::route('/create'),
            'edit' => EditCabysCode::route('/{record}/edit'),
        ];
    }
}
