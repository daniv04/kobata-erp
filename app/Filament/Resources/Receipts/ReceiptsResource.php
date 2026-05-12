<?php

namespace App\Filament\Resources\Receipts;

use App\Filament\Resources\Receipts\Pages\CreateReceipts;
use App\Filament\Resources\Receipts\Pages\EditReceipts;
use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Schemas\ReceiptsForm;
use App\Filament\Resources\Receipts\Tables\ReceiptsTable;
use App\Models\Receipts;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceiptsResource extends Resource
{
    protected static ?string $model = Receipts::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReceiptsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptsTable::configure($table);
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
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipts::route('/create'),
            'edit' => EditReceipts::route('/{record}/edit'),
        ];
    }
}
